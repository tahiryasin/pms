<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskStatusFilter;

use DB;

class TaskStatusFilter implements TaskStatusFilterInterface
{
    public function getFilter(int $project_id, bool $is_for_client): array
    {
        $client_condition = $is_for_client ? ' AND is_hidden_from_clients = 0' : '';

        $open_tasks = (int) DB::executeFirstCell(
            'SELECT COUNT(id) AS open_tasks FROM tasks WHERE project_id = ? AND is_trashed = ?  AND completed_on IS NULL' . $client_condition,
            $project_id,
            false
        );

        $completed_tasks = (int) DB::executeFirstCell(
            'SELECT COUNT(id) AS completed_tasks FROM tasks WHERE project_id = ? AND is_trashed = ? AND completed_on IS NOT NULL' . $client_condition,
            $project_id,
            false
        );

        return [
            [
                'id' => self::TASK_STATUS_FILTER_ALL,
                'name' => lang('All Tasks'),
                'count' => $open_tasks + $completed_tasks,
                'is_savable' => true,
            ],
            [
                'id' => self::TASK_STATUS_FILTER_OPEN,
                'name' => lang('Open Tasks'),
                'count' => $open_tasks,
                'is_savable' => true,
            ],
            [
                'id' => self::TASK_STATUS_FILTER_COMPLETED,
                'name' => lang('Completed Tasks'),
                'count' => $completed_tasks,
                'is_savable' => false,
            ],
        ];
    }
}
