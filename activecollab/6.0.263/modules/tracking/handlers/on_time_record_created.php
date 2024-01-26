<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_time_record_created event handler.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage handlers
 */

/**
 * Handle on_time_record_created_event.
 *
 * @param TimeRecord $time_record
 */
function tracking_handle_on_time_record_created(TimeRecord $time_record)
{
    Webhooks::dispatch($time_record, 'TimeRecordCreated');
}
