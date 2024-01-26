<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Metric\ActivityCollection;
use ActiveCollab\Module\System\Metric\CompaniesCollection;
use ActiveCollab\Module\System\Metric\HasCompletedOnboardingSurveyFlag;
use ActiveCollab\Module\System\Metric\NumberOfCalendarEventsCounter;
use ActiveCollab\Module\System\Metric\StorageCollection;
use ActiveCollab\Module\System\Metric\TimeToOnboardingSurveyCompletionTimer;
use ActiveCollab\Module\System\Metric\UsersCollection;

/**
 * Handle on_extra_stats event.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * @param array     $stats
 * @param DateValue $date
 */
function system_handle_on_extra_stats(array &$stats, $date)
{
    $stats['active_owners'] = 0;
    $stats['archived_owners'] = 0;
    $stats['active_members'] = 0;
    $stats['archived_members'] = 0;
    $stats['active_clients'] = 0;
    $stats['archived_clients'] = 0;

    $inc_user_stats = function (array &$stats, array $row, $active_key, $archived_key) {
        if ($row['is_archived']) {
            ++$stats[$archived_key];
        } else {
            ++$stats[$active_key];
        }
    };

    $is_client_plus = function (array $row) {
        if (!empty($row['raw_additional_properties'])) {
            $values = unserialize($row['raw_additional_properties']);

            if (isset($values['custom_permissions'])
                && in_array(User::CAN_MANAGE_TASKS, $values['custom_permissions'])
            ) {
                return true;
            }
        }

        return false;
    };

    if ($rows = DB::execute(
        "SELECT `type`, COUNT(`id`) as 'row_count', `is_archived`, `raw_additional_properties`
            FROM `users`
            WHERE `is_trashed` = ? AND DATE(`created_on`) <= ?
            GROUP BY `type`, `is_archived`, `raw_additional_properties`",
        false,
        $date
    )) {
        foreach ($rows as $row) {
            switch ($row['type']) {
                case Owner::class:
                    $inc_user_stats($stats, $row, 'active_owners', 'archived_owners');
                    break;
                case Member::class:
                    $inc_user_stats($stats, $row, 'active_members', 'archived_members');
                    break;
                default:
                    if ($is_client_plus($row)) {
                        $inc_user_stats($stats, $row, 'active_members', 'archived_members');
                    } else {
                        $inc_user_stats($stats, $row, 'active_clients', 'archived_clients');
                    }
            }
        }
    }

    $stats['last_user_visit'] = DB::executeFirstCell(
        'SELECT MAX(last_used_on) AS "last_used_on" FROM user_sessions WHERE DATE(created_on) <= ?',
        $date
    );

    $stats['active_projects'] = DB::executeFirstCell(
        'SELECT COUNT(id) AS "row_count" FROM projects WHERE is_trashed = ? AND completed_on IS NULL AND DATE(created_on) <= ?',
        false,
        $date
    );
    $stats['completed_projects'] = DB::executeFirstCell(
        'SELECT COUNT(id) AS "row_count" FROM projects WHERE is_trashed = ? AND completed_on IS NOT NULL AND DATE(created_on) <= ?',
        false,
        $date
    );

    $stats['total_tasks'] = DB::executeFirstCell(
        'SELECT COUNT(id) AS "row_count" FROM tasks WHERE is_trashed = ? AND DATE(created_on) <= ?',
        false,
        $date
    );
    $stats['open_tasks'] = DB::executeFirstCell(
        'SELECT COUNT(id) AS "row_count" FROM tasks WHERE is_trashed = ? AND DATE(created_on) = ?',
        false,
        $date
    );
    $stats['completed_tasks'] = DB::executeFirstCell(
        'SELECT COUNT(id) AS "row_count" FROM tasks WHERE is_trashed = ? AND DATE(completed_on) = ?',
        false,
        $date
    );

    (new ActivityCollection())->getValueFor($date)->addTo($stats);
    (new CompaniesCollection(
        DB::getConnection(),
        (int) Companies::getOwnerCompanyId()
    ))->getValueFor($date)->addTo($stats);
    (new UsersCollection(DB::getConnection()))->getValueFor($date)->addTo($stats);

    (new StorageCollection(
        AngieApplication::storage(),
        AngieApplication::storageCapacityCalculator()
    ))->getValueFor($date)->addTo($stats);

    (new NumberOfCalendarEventsCounter())->getValueFor($date)->addTo($stats);

    if (AngieApplication::isOnDemand()) {
        (new HasCompletedOnboardingSurveyFlag())->getValueFor($date)->addTo($stats);
        (new TimeToOnboardingSurveyCompletionTimer())->getValueFor($date)->addTo($stats);
    }
}
