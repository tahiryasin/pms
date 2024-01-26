<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Tasks module on_object_from_notification_context events handler.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * @param null   $object
 * @param string $name
 * @param int    $id
 */
function tasks_handle_on_object_from_notification_context(&$object, $name, $id)
{
    switch ($name) {
        case 'task':
            $object = DataObjectPool::get('Task', $id);
            break;
        case 'task-list':
            $object = DataObjectPool::get('TaskList', $id);
            break;
    }
}
