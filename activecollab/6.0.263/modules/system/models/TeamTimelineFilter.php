<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Projects filter.
 */
class TeamTimelineFilter extends DataFilter
{
    /**
     * @var AssignmentFilter
     */
    public $filter;

    /**
     * {@inheritdoc}
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->filter = new AssignmentFilter();
    }

    /**
     * {@inheritdoc}
     */
    public function getExportColumns()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function exportWriteLines(User $user, array &$result)
    {
        return [];
    }

    /**
     * Run the filter.
     *
     * @param User $user
     * @param null $additional
     *
     * @return Dataobject[]|DBResult
     */
    public function run(User $user, $additional = null)
    {
        $this->filter->setIncludeTrackingData(true);
        $this->filter->setCompletedOnFilter(AssignmentFilter::DATE_FILTER_IS_NOT_SET);
        $this->filter->setProjectFilter(Projects::PROJECT_FILTER_ACTIVE);
        $this->filter->setGroupBy(
            AssignmentFilter::GROUP_BY_ASSIGNEE,
            AssignmentFilter::GROUP_BY_PROJECT
        );

        if ($result = $this->filter->run($user, $additional)) {
            return $this->prepareResults($result);
        }

        return null;
    }

    /**
     * Return true if $user can run this report.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canRun(User $user)
    {
        return $user->isPowerUser();
    }

    /**
     * Set non-field value during DataManager::create() and DataManager::update() calls.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function setAttribute($attribute, $value)
    {
        $this->filter->setAttribute($attribute, $value);
    }

    /**
     * Remove unused element from array if is settled.
     *
     * @param array $results
     *
     * @return Dataobject[]|DBResult
     */
    private function prepareResults(array $results)
    {
        foreach ($results as $key => $result) {
            if (isset($results[$key]['assignments']['unknow-project'])) {
                unset($results[$key]['assignments']['unknow-project']);
            }
        }

        return $results;
    }
}
