<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * User time records collection.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class UserTimeRecordsCollection extends TimeRecordsCollection
{
    /**
     * @var DateValue
     */
    private $from_date;
    private $to_date;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var string
     */
    private $query_conditions = false;

    /**
     * Construct the collection.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $bits = explode('_', $name);

        if (str_starts_with($name, 'filtered_time_records_by_user')) {
            [$this->from_date, $this->to_date] = $this->prepareFromToFromCollectionName($bits);
        } else {
            $this->preparePaginationFromCollectionName($bits);
        }

        $this->user_id = $this->prepareIdFromCollectionName($bits);
    }

    /**
     * Prepare query conditions.
     *
     * @return string
     * @throws ImpossibleCollectionError
     */
    protected function getQueryConditions()
    {
        if ($this->query_conditions === false) {
            $whos_asking = $this->getWhosAsking();

            if ($whos_asking instanceof Client) {
                throw new ImpossibleCollectionError();
            }

            $user = DataObjectPool::get(User::class, $this->user_id);

            if ($user instanceof User) {
                // ---------------------------------------------------
                //  Only owner can see time sheets of other users
                // ---------------------------------------------------

                if ($user->getId() != $whos_asking->getId() && !$whos_asking->isOwner()) {
                    throw new ImpossibleCollectionError();
                }

                // ---------------------------------------------------
                //  Prepare conditions
                // ---------------------------------------------------

                if ($project_ids = Projects::findIdsByUser($user, true, ['is_trashed = ?', false])) {
                    $conditions = [DB::prepare('(user_id = ? AND is_trashed = ?)', $user->getId(), false)]; // User's untrashed records

                    if ($this->from_date && $this->to_date) {
                        $conditions[] = DB::prepare('(record_date BETWEEN ? AND ?)', $this->from_date, $this->to_date, false);
                    }

                    $project_ids = DB::escape($project_ids);
                    $task_subquery = DB::prepare("SELECT id FROM tasks WHERE project_id IN ($project_ids) AND is_trashed = ?", false);

                    $conditions[] = DB::prepare("((parent_type = 'Project' AND parent_id IN ($project_ids)) OR (parent_type = 'Task' AND parent_id IN ($task_subquery)))");
                } else {
                    throw new ImpossibleCollectionError();
                }

                $this->query_conditions = implode(' AND ', $conditions);
            } else {
                throw new ImpossibleCollectionError();
            }
        }

        return $this->query_conditions;
    }

    /**
     * Return how time records should be ordered.
     *
     * @return string
     */
    protected function getOrderBy()
    {
        return $this->from_date && $this->to_date ? 'id' : 'record_date DESC, id DESC';
    }
}
