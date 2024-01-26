<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_calculate_storage_usage event handler implementation.
 *
 * @package ActiveCollab.modules.files
 * @subpackage handlers
 */

/**
 * Handle on_calculate_storage_usage event.
 *
 * @param int       $storage_used
 * @param DateValue $date
 */
function files_handle_on_calculate_storage_usage(&$storage_used, $date)
{
    $storage_used += (int) DB::executeFirstCell('SELECT SUM(size) FROM files WHERE DATE(created_on) <= ?', $date);
}
