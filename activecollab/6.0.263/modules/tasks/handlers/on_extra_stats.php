<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\Tasks\Metric\AddedTasksCounter;
use ActiveCollab\Module\Tasks\Metric\CompletedTasksCounter;
use ActiveCollab\Module\Tasks\Metric\TasksCollection;

/**
 * Handle on_extra_stats event.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * @param array     $stats
 * @param DateValue $date
 */
function tasks_handle_on_extra_stats(array &$stats, $date)
{
    (new TasksCollection(DB::getConnection()))->getValueFor($date)->addTo($stats);
    (new AddedTasksCounter(DB::getConnection()))->getValueFor($date)->addTo($stats);
    (new CompletedTasksCounter(DB::getConnection()))->getValueFor($date)->addTo($stats);
}
