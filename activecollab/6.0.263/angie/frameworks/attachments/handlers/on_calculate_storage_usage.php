<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_calculate_storage_usage event handler implementation.
 *
 * @package angie.frameworks.attachments
 * @subpackage handlers
 */

/**
 * Handle on_calculate_storage_usage event.
 *
 * @param int       $storage_used
 * @param DateValue $date
 */
function attachments_handle_on_calculate_storage_usage(&$storage_used, $date)
{
    $storage_used += (int) DB::executeFirstCell('SELECT SUM(size) FROM attachments WHERE DATE(created_on) <= ?', $date);
    $storage_used += (int) DB::executeFirstCell('SELECT SUM(size) FROM uploaded_files WHERE DATE(created_on) <= ?', $date);
}
