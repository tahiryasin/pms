<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Metric;

use Angie\Inflector;
use Angie\Metric\Collection;
use DateValue;
use DB;

final class ActivityCollection extends Collection
{
    public function getValueFor(DateValue $date)
    {
        [
            $from_timestamp,
            $to_timestamp,
        ] = $this->dateToRange($date);

        $number_of_activities = DB::executeFirstCell(
            'SELECT COUNT(`id`) AS "row_count" FROM `activity_logs` WHERE `created_on` BETWEEN ? AND ?',
            $from_timestamp,
            $to_timestamp
        );

        $activities_by_hour = [];
        $activities_by_type = [];
        $activities_by_user = [];

        if ($number_of_activities) {
            $activities_by_hour = $this->getActivitiesBy(
                'HOUR(`created_on`)',
                'intval',
                true,
                $from_timestamp,
                $to_timestamp
            );

            $activities_by_type = $this->getActivitiesBy(
                'CONCAT(`parent_type`, `type`)',
                function ($concated_type) {
                    return Inflector::underscore(str_replace(
                        [
                            'Instance',
                            'ActivityLog',
                        ],
                        [
                            '',
                            '',
                        ],
                        $concated_type
                    ));
                },
                false,
                $from_timestamp,
                $to_timestamp
            );

            $activities_by_user = $this->getActivitiesBy(
                '`created_by_id`',
                'intval',
                true,
                $from_timestamp,
                $to_timestamp
            );
        }

        $active_users = array_keys($activities_by_user);
        $visiting_users = $this->getVisitingUsers($active_users, $from_timestamp, $to_timestamp);
        $api_users = $this->getApiUsers($from_timestamp, $to_timestamp);

        return $this->produceResult(
            [
                'was_active' => (bool) $number_of_activities,
                'had_visits' => !empty($visiting_users),
                'had_api_activity' => !empty($api_users),
                'visiting_users' => $visiting_users,
                'active_users' => $active_users,
                'api_users' => $api_users,
                'number_of_activities' => $number_of_activities,
                'activities_by_hour' => $activities_by_hour,
                'activities_by_type' => $activities_by_type,
                'activities_by_user' => $activities_by_user,
            ],
            $date
        );
    }

    private function getActivitiesBy($group_by, $dimension_caster, $sort_by_dimension, $from_timestamp, $to_timestamp)
    {
        $result = [];

        if ($rows = DB::execute(
            "SELECT COUNT(`id`) AS 'activities_count', {$group_by} AS 'dimension'
                    FROM `activity_logs`
                    WHERE `created_on` BETWEEN ? AND ?
                    GROUP BY `dimension`",
            $from_timestamp,
            $to_timestamp
        )) {
            foreach ($rows as $row) {
                $result[call_user_func($dimension_caster, $row['dimension'])] = (int) $row['activities_count'];
            }

            if ($sort_by_dimension) {
                ksort(
                    $result,
                    $dimension_caster === 'intval' ? SORT_NUMERIC : SORT_NATURAL
                );
            }
        }

        return $result;
    }

    private function getVisitingUsers($active_users, $from_timestamp, $to_timestamp)
    {
        $result = $active_users;

        // Query access log.
        $accessed_by_id_conditions = empty($result) ?
            DB::prepare('`accessed_by_id` > ?', 0) :
            DB::prepare('`accessed_by_id` > ? AND `accessed_by_id` NOT IN (?)', 0, $result);

        $users_from_access_log = DB::executeFirstColumn(
            "SELECT DISTINCT `accessed_by_id`
                FROM `access_logs`
                WHERE {$accessed_by_id_conditions} AND `accessed_on` BETWEEN ? AND ?",
            $from_timestamp,
            $to_timestamp
        );

        if (!empty($users_from_access_log)) {
            $result = array_merge($result, $users_from_access_log);
        }

        $user_id_conditions = empty($result) ?
            DB::prepare('`user_id` > ?', 0) :
            DB::prepare('`user_id` > ? AND `user_id` NOT IN (?)', 0, $result);

        $users_from_user_sessions = DB::executeFirstColumn(
            "SELECT DISTINCT `user_id`
                FROM `user_sessions`
                WHERE {$user_id_conditions} AND (`created_on` BETWEEN ? AND ? OR `last_used_on` BETWEEN ? AND ?)",
            $from_timestamp,
            $to_timestamp,
            $from_timestamp,
            $to_timestamp
        );

        if (!empty($users_from_user_sessions)) {
            $result = array_merge($result, $users_from_user_sessions);
        }

        sort($result);

        return $result;
    }

    private function getApiUsers($from_timestamp, $to_timestamp)
    {
        $result = DB::executeFirstColumn(
            'SELECT DISTINCT `user_id`
                FROM `api_subscriptions`
                WHERE `last_used_on` BETWEEN ? AND ?',
            $from_timestamp,
            $to_timestamp
        );

        if (empty($result)) {
            $result = [];
        }

        return $result;
    }
}
