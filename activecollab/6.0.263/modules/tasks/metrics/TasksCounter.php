<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Metric;

use Angie\Metric\Counter;
use DateValue;
use DBConnection;

abstract class TasksCounter extends Counter
{
    protected $connection;

    public function __construct(DBConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getValueFor(DateValue $date)
    {
        [$from_timestamp, $until_timestamp] = $this->dateToRange($date);

        $conditions = [
            $this->connection->prepare(
                '`is_trashed` = ?',
                [
                    false,
                ]
            ),
        ];

        $conditions = implode(' AND ', $this->prepareConditions($from_timestamp, $until_timestamp, $conditions));

        return $this->produceResult(
            $this->connection->executeFirstCell(
                "SELECT COUNT(`id`) AS 'row_count' FROM `tasks` WHERE {$conditions}"
            ),
            $date
        );
    }

    abstract protected function prepareConditions($from_timestamp, $until_timestamp, array $conditions);
}
