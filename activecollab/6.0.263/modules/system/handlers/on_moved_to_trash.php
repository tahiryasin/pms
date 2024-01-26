<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_moved_to_trash event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_moved_to_trash event.
 *
 * @param DataObject $object
 */
function system_handle_on_moved_to_trash(DataObject $object)
{
    Webhooks::dispatch($object, get_class($object) . 'MovedToTrash');
}
