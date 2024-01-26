<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Open assignments for user collection.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
class OpenAssignmentsForAssigneeCollection extends AssignmentsCollection
{
    /**
     * @var User
     */
    private $assignee;

    /**
     * @var ModelCollection
     */
    private $tasks_collection;
    private $subtasks_collection;

    /**
     * @var int[]
     */
    private $task_ids = false;

    /**
     * Set assignee.
     *
     * @param  User              $assignee
     * @return $this
     * @throws InvalidParamError
     */
    public function &setAssignee(User $assignee)
    {
        if ($assignee instanceof User) {
            $this->assignee = $assignee;
        } else {
            throw new InvalidParamError('assignee', $assignee, 'User');
        }

        return $this;
    }

    /**
     * Return user or team timestamp.
     *
     * @return string
     */
    public function getContextTimestamp()
    {
        return $this->assignee->getUpdatedOn()->toMySQL();
    }

    /**
     * Return model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Users';
    }

    /**
     * Return assigned tasks collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getTasksCollections()
    {
        if (empty($this->tasks_collection)) {
            if ($this->assignee instanceof User && $this->getWhosAsking() instanceof User) {
                if ($task_ids = $this->getTaskIds()) {
                    Comments::preloadCountByParents('Task', $task_ids);
                    Subtasks::preloadCountByTasks($task_ids);
                    Attachments::preloadDetailsByParents('Task', $task_ids);
                    Labels::preloadDetailsByParents('Task', $task_ids);
                    TaskDependencies::preloadCountByTasks($task_ids);
                }

                $this->tasks_collection = Tasks::prepareCollection('open_tasks_assigned_to_user_' . $this->assignee->getId(), $this->getWhosAsking());
            } else {
                throw new ImpossibleCollectionError("Invalid user and/or who's asking instance");
            }
        }

        return $this->tasks_collection;
    }

    /**
     * Return assigned subtasks collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getSubtasksCollection()
    {
        if (empty($this->subtasks_collection)) {
            if ($this->assignee instanceof User && $this->getWhosAsking() instanceof User) {
                $this->subtasks_collection = Subtasks::prepareCollection('open_subtasks_assigned_to_user_' . $this->assignee->getId(), $this->getWhosAsking());
            } else {
                throw new ImpossibleCollectionError("Invalid user and/or who's asking instance");
            }
        }

        return $this->subtasks_collection;
    }

    /**
     * Return list of task ID-s that are captured by this collection.
     *
     * @return array|null
     */
    private function getTaskIds()
    {
        if ($this->task_ids === false) {
            $this->task_ids = DB::executeFirstColumn('SELECT id FROM tasks WHERE assignee_id = ? AND completed_on IS NULL AND is_trashed = ?', $this->assignee->getId(), false);
        }

        return $this->task_ids;
    }
}
