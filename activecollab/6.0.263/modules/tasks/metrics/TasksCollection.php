<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Metric;

use Angie\Metric\Collection;
use DateValue;
use DBConnection;
use Task;

final class TasksCollection extends Collection
{
    private $connection;

    public function __construct(DBConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getValueFor(DateValue $date)
    {
        $until_timestamp = $this->dateToRange($date)[1];

        [
            $total_tasks,
            $assigned_tasks,
            $tasks_with_estimate,
            $hidden_from_clients
        ] = $this->countTasksWithProperties($until_timestamp);

        return $this->produceResult(
            [
                'total' => $total_tasks,
                'open' => $this->countOpenTasks($until_timestamp),
                'completed' => $this->countCompletedTasks($until_timestamp),
                'assigned' => $assigned_tasks,
                'with_estimate' => $tasks_with_estimate,
                'with_subtasks' => $this->countTasksWithSubtasks($until_timestamp),
                'with_comments' => $this->countTasksWithComments($until_timestamp),
                'with_time_records' => $this->countTasksWithTimeRecords($until_timestamp),
                'with_expenses' => $this->countTasksWithExpenses($until_timestamp),
                'hidden_from_clients' => $hidden_from_clients,
            ],
            $date
        );
    }

    private function countTasksWithProperties($until_timestamp)
    {
        $total_tasks = 0;
        $assigned_tasks = 0;
        $tasks_with_estimate = 0;
        $hidden_from_clients = 0;

        if ($rows = $this->connection->execute(
            "SELECT
                COUNT(`id`) AS 'rows_count',
                (`assignee_id` > 0) AS 'is_assigned',
                (`estimate` > 0) AS 'has_estimate',
                `is_hidden_from_clients`
                FROM `tasks`
                WHERE `created_on` <= ? AND `is_trashed` = ?
                GROUP BY `is_assigned`, `has_estimate`, `is_hidden_from_clients`",
            [
                $until_timestamp,
                false,
            ]
        )) {
            foreach ($rows as $row) {
                $total_tasks += $row['rows_count'];

                if ($row['is_assigned']) {
                    $assigned_tasks += $row['rows_count'];
                }

                if ($row['has_estimate']) {
                    $tasks_with_estimate += $row['rows_count'];
                }

                if ($row['is_hidden_from_clients']) {
                    $hidden_from_clients += $row['rows_count'];
                }
            }
        }

        return [
            $total_tasks,
            $assigned_tasks,
            $tasks_with_estimate,
            $hidden_from_clients,
        ];
    }

    private function countOpenTasks($until_timestamp)
    {
        return $this->connection->executeFirstCell(
            'SELECT COUNT(`id`) AS "row_count"
                    FROM `tasks`
                    WHERE `created_on` <= ?
                        AND (`completed_on` IS NULL OR `completed_on` > ?)
                        AND `is_trashed` = ?',
            [
                $until_timestamp,
                $until_timestamp,
                false,
            ]
        );
    }

    private function countCompletedTasks($until_timestamp)
    {
        return $this->connection->executeFirstCell(
            'SELECT COUNT(`id`) AS "row_count"
                    FROM `tasks`
                    WHERE `created_on` <= ?
                        AND `completed_on` <= ?
                        AND `is_trashed` = ?',
            [
                $until_timestamp,
                $until_timestamp,
                false,
            ]
        );
    }

    private function countTasksWithSubtasks($until_timestamp)
    {
        return $this->connection->executeFirstCell(
            'SELECT COUNT(DISTINCT `task_id`) AS "row_count"
                FROM `subtasks`, `tasks`
                WHERE `subtasks`.`task_id` = `tasks`.`id`
                    AND `subtasks`.`created_on` <= ?
                    AND `subtasks`.`is_trashed` = ?',
            [
                $until_timestamp,
                false,
            ]
        );
    }

    private function countTasksWithComments($until_timestamp)
    {
        return $this->countTasksWithRelatedObjects('comments', $until_timestamp);
    }

    private function countTasksWithTimeRecords($until_timestamp)
    {
        return $this->countTasksWithRelatedObjects('time_records', $until_timestamp);
    }

    private function countTasksWithExpenses($until_timestamp)
    {
        return $this->countTasksWithRelatedObjects('expenses', $until_timestamp);
    }

    private function countTasksWithRelatedObjects($table_name, $until_timestamp)
    {
        return $this->connection->executeFirstCell(
            'SELECT COUNT(DISTINCT `parent_id`) AS "row_count"
                FROM `' . $table_name . '` related_objects, `tasks`
                WHERE related_objects.`parent_type` = ?
                    AND related_objects.`parent_id` = `tasks`.`id`
                    AND related_objects.`created_on` <= ?
                    AND related_objects.`is_trashed` = ?;',
            [
                Task::class,
                $until_timestamp,
                false,
            ]
        );
    }
}
