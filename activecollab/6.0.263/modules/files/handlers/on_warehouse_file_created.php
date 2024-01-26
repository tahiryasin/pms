<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_file_created event handler.
 *
 * @package ActiveCollab.modules.files
 * @subpackage handlers
 */

/**
 * Handle on_file_created event.
 *
 * @param File  $file
 * @param array $attributes
 */
function files_handle_on_warehouse_file_created(File $file, array $attributes)
{
    Webhooks::dispatch($file, 'FileCreated');
}
