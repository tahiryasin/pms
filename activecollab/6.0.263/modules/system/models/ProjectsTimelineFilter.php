<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Reports\Report;

/**
 * Projects filter.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class ProjectsTimelineFilter extends DataFilter
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
     * {@inheritdoc}
     */
    public function run(User $user, $additional = null)
    {
        $this->filter->setExtendTaskListNameWhenGrouping(false);
        $this->filter->setCompletedOnFilter(AssignmentFilter::DATE_FILTER_IS_NOT_SET);
        $this->filter->setGroupBy(AssignmentFilter::GROUP_BY_PROJECT, AssignmentFilter::GROUP_BY_TASK_LIST);

        return $this->filter->run($user, $additional);
    }

    /**
     * Return true if $user can run this report.
     *
     * @param  User $user
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
}
