<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskStatusFilter;

interface TaskStatusFilterInterface
{
    const TASK_STATUS_FILTER_OPEN = 'open';
    const TASK_STATUS_FILTER_COMPLETED = 'completed';
    const TASK_STATUS_FILTER_ALL = 'all';

    const TASK_STATUS_FILTERS = [
        self::TASK_STATUS_FILTER_OPEN,
        self::TASK_STATUS_FILTER_COMPLETED,
        self::TASK_STATUS_FILTER_ALL,
    ];

    public function getFilter(int $project_id, bool $is_for_client): array;
}
