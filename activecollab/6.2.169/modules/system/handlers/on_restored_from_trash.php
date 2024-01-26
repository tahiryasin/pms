<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_restored_from_trash event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_restored_from_trash event.
 *
 * @param DataObject $object
 */
function system_handle_on_restored_from_trash(DataObject $object)
{
    Webhooks::dispatch($object, get_class($object) . 'RestoredFromTrash');
}
