<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskListFilter;

use DB;

class TaskListFilter implements TaskListFilterInterface
{
    public function getFilter(int $project_id, bool $is_for_client, ?string $tasks_status = 'open'): array
    {
        $sql = 'SELECT tl.id, tl.position, tl.name, count(t.id) AS count
                FROM task_lists tl
                LEFT JOIN tasks t ON t.task_list_id = tl.id
                WHERE tl.project_id = ? AND tl.is_trashed = ? AND t.is_trashed = ?';

        if ($tasks_status) {
            if ($tasks_status === 'open') {
                $sql .= ' AND tl.completed_on IS NULL AND t.completed_on IS NULL';
            } elseif ($tasks_status === 'completed') {
                $sql .= ' AND tl.completed_on IS NOT NULL AND t.completed_on IS NOT NULL';
            }
        }

        if($is_for_client) {
            $sql = $sql . ' AND t.is_hidden_from_clients = 0';
        }

        $sql .= ' GROUP BY tl.id';

        $task_lists_with_tasks = DB::execute($sql, $project_id, false, false);

        $task_lists = $task_lists_with_tasks ? $task_lists_with_tasks->toArray() : [];

        $sql = 'SELECT tl.id as `list_id`, tl.name, tl.position, t.id
              FROM task_lists AS tl
              LEFT JOIN tasks AS t
              ON t.task_list_id = tl.id
              WHERE tl.project_id = ?
              AND tl.is_trashed = ?
              AND t.id IS NULL
              GROUP BY tl.id, t.id';

        $result = DB::execute($sql, $project_id, false);
        $task_lists_without_tasks = $result ? $result->toArray() : [];

        if($task_lists_without_tasks) {
            foreach ($task_lists_without_tasks as $task_list) {
                array_push(
                    $task_lists,
                    [
                        'id' => $task_list['list_id'],
                        'position' => $task_list['position'],
                        'name' => $task_list['name'],
                        'count' => 0,
                    ]
                );
            }
        }

        return $task_lists ? $task_lists : [];
    }
}
