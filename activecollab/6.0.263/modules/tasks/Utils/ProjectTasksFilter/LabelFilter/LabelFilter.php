<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\LabelFilter;

use DB;
use Task;

class LabelFilter implements LabelFilterInterface
{
    public function getFilter(int $project_id, bool $is_for_client, string $tasks_status = 'open'): array
    {
        $label_filters = $this->getLabels($project_id, $is_for_client, $tasks_status);
        $not_set_data = $this->getNotSetLabels($project_id, $is_for_client, $tasks_status);

        if ($not_set_data['count'] > 0) {
            array_push($label_filters, $not_set_data);
        }

        return $label_filters;
    }

    private function getLabels(int $project_id, bool $is_for_client, string $tasks_status): array
    {
        $sql = 'SELECT l.id, l.name, count(*) as count
            FROM tasks as t 
            LEFT JOIN parents_labels as pl ON t.id = pl.parent_id 
            LEFT JOIN labels as l ON l.id = pl.label_id
            WHERE t.project_id = ? AND pl.parent_type = ? AND t.is_trashed = ?';

        if ($is_for_client) {
            $sql .= ' AND t.is_hidden_from_clients = 0';
        }

        if ($tasks_status) {
            if ($tasks_status === 'open') {
                $sql .= ' AND t.completed_on IS NULL';
            } elseif ($tasks_status === 'completed') {
                $sql .= ' AND t.completed_on IS NOT NULL';
            }
        }

        $sql .= ' GROUP BY l.name,l.id ORDER BY count DESC';

        $data = DB::execute($sql, $project_id, Task::class, false);

        return $data ? $data->toArray() : [];
    }

    private function getNotSetLabels(int $project_id, bool $is_for_client, string $tasks_status): array
    {
        $not_set = [
            'id' => -1,
            'name' => lang('Not Set'),
            'count' => 0,
        ];

        $sql = 'SELECT count(DISTINCT t.id) as `not_set`
                FROM tasks t 
                LEFT JOIN `parents_labels` pl ON pl.parent_id = t.id AND pl.parent_type = ?
                WHERE pl.label_id IS NULL AND t.project_id = ? AND t.is_trashed = ?';

        if ($is_for_client) {
            $sql .= 'AND t.is_hidden_from_clients = 0';
        }

        if ($tasks_status) {
            if ($tasks_status === 'open') {
                $sql .= ' AND t.completed_on IS NULL';
            } elseif ($tasks_status === 'completed') {
                $sql .= ' AND t.completed_on IS NOT NULL';
            }
        }

        $data = DB::execute($sql, Task::class, $project_id, false);

        if ($data) {
            $not_set['count'] = intval($data->toArray()[0]['not_set']);
        }

        return $not_set;
    }
}
