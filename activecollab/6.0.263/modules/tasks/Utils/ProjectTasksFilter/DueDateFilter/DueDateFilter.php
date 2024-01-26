<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\DueDateFilter;

use ActiveCollab\DateValue\DateValue;
use ConfigOptions;
use DB;
use IUser;

class DueDateFilter implements DueDateFilterInterface
{
    public function getFilter(int $project_id, IUser $user, string $tasks_status = 'open'): array
    {
        $time_zone = ConfigOptions::getValueFor('time_timezone', $user);

        $filters = [
            [
                'id' => self::TASK_FILTER_ID_TODAY,
                'name' => lang('Today'),
                'count' => 0,
            ],
            [
                'id' => self::TASK_FILTER_ID_OVERDUE,
                'name' => lang('Overdue'),
                'count' => 0,
            ],
            [
                'id' => self::TASK_FILTER_ID_UPCOMING,
                'name' => lang('Less than 1 week'),
                'count' => 0,
            ],
            [
                'id' => self::TASK_FILTER_ID_SCHEDULED,
                'name' => lang('More than 1 week'),
                'count' => 0,
            ],
            [
                'id' => self::TASK_FILTER_ID_NOT_SET,
                'name' => lang('Not Set'),
                'count' => 0,
            ],
        ];

        /** @var DateValue $seven_days_from_now */
        $seven_days_from_now = (new DateValue())->addDays(7);

        $time_zone ? $seven_days_from_now->timezone($time_zone) : $seven_days_from_now->timezone('UTC');

        foreach ($this->getTasks($project_id, $user, $tasks_status) as $item) {
            if (array_key_exists('due_on', $item) && $item['due_on']) {
                /** @var DateValue $due_on */
                $due_on = new DateValue($item['due_on']);
                $time_zone ? $due_on->timezone($time_zone) : $due_on->timezone('UTC');

                if ($due_on->isToday()) {
                    ++$filters[0]['count'];
                }

                if ($due_on->isPast()) {
                    ++$filters[1]['count'];
                }

                if (!$due_on->isPast() && !$due_on->isToday() && ($due_on->getTimestamp() <= $seven_days_from_now->getTimestamp())) {
                    ++$filters[2]['count'];
                }

                if (!$due_on->isPast() && !$due_on->isToday() && ($due_on->getTimestamp() > $seven_days_from_now->getTimestamp())) {
                    ++$filters[3]['count'];
                }
            } else {
                ++$filters[4]['count'];
            }
        }

        return $this->getExistingDueDateFilters($filters);
    }

    private function getExistingDueDateFilters(array $filters): array
    {
        foreach ($filters as $index => $filter) {
            if ($filter['count'] < 1) {
                unset($filters[$index]);
            }
        }

        usort($filters, function ($a, $b) {
            return $b['count'] > $a['count'];
        });

        return $filters;
    }

    private function getTasks(int $project_id, IUser $user, string $tasks_status): array
    {
        $sql = 'SELECT due_on FROM tasks WHERE project_id = ? AND is_trashed = ?';

        if ($user->isClient()) {
            $sql .= ' AND is_hidden_from_clients = 0';
        }

        if ($tasks_status) {
            if ($tasks_status === 'open') {
                $sql .= ' AND completed_on IS NULL';
            } elseif ($tasks_status === 'completed') {
                $sql .= ' AND completed_on IS NOT NULL';
            }
        }

        $data = DB::execute($sql, $project_id, false);

        return $data ? $data->toArray() : [];
    }
}
