<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\DueDateFilter;

use IUser;

interface DueDateFilterInterface
{
    const TASK_FILTER_ID_TODAY = 'today';
    const TASK_FILTER_ID_OVERDUE = 'overdue';
    const TASK_FILTER_ID_UPCOMING = 'upcoming';
    const TASK_FILTER_ID_SCHEDULED = 'scheduled';
    const TASK_FILTER_ID_NOT_SET = 'not_set';

    const TASK_FILTER_IDS = [
        self::TASK_FILTER_ID_TODAY,
        self::TASK_FILTER_ID_OVERDUE,
        self::TASK_FILTER_ID_UPCOMING,
        self::TASK_FILTER_ID_SCHEDULED,
        self::TASK_FILTER_ID_NOT_SET,
    ];

    public function getFilter(int $project_id, IUser $user, string $tasks_status = 'open'): array;
}
