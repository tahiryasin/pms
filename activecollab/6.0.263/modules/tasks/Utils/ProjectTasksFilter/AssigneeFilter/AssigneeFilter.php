<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\AssigneeFilter;

use DB;

class AssigneeFilter implements AssigneeFilterInterface
{
    public function getFilter(int $project_id, bool $is_for_client, string $tasks_status = 'open'): array
    {
        $filters = [];
        $not_set = [
            'id' => 0,
            'name' => lang('Unassigned'),
            'count' => 0,
        ];

        foreach ($this->getLabels($project_id, $is_for_client, $tasks_status) as $item) {
            if (array_key_exists('assignee_id', $item) && $item['assignee_id'] > 0) {
                $assignee_id = $item['assignee_id'];
                $assignee_name = $this->getAssigneeName($item);

                if (array_key_exists($assignee_id, $filters)) {
                    ++$filters[$assignee_id]['count'];
                } else {
                    $filters[$assignee_id]['id'] = $assignee_id;
                    $filters[$assignee_id]['name'] = $assignee_name;
                    $filters[$assignee_id]['count'] = 1;
                }
            } else {
                ++$not_set['count'];
            }
        }

        if ($not_set['count'] > 0) {
            array_push($filters, $not_set);
        }

        usort($filters, function ($a, $b) {
            return $b['count'] > $a['count'];
        });

        return $filters;
    }

    private function getAssigneeName($data): string
    {
        $name = 'No name and email';

        if ((array_key_exists('first_name', $data) && array_key_exists('last_name', $data))
            || array_key_exists('email', $data)
        ) {
            $name = $data['first_name'] && $data['last_name']
                ? $data['first_name'] . ' ' . $data['last_name']
                : $data['email'];
        }

        return $name;
    }

    private function getLabels(int $project_id, bool $is_for_client, string $tasks_status): array
    {
        $sql = 'SELECT t.assignee_id, u.first_name, u.last_name, u.email 
                FROM tasks t
                LEFT JOIN users u ON u.id = t.assignee_id
                WHERE t.project_id = ? AND t.is_trashed = ?';

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

        $data = DB::execute($sql, $project_id, false);

        return $data ? $data->toArray() : [];
    }
}
