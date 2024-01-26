<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_task_updated event handler.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * Handle on_task_updated event.
 *
 * @param Task  $task
 * @param array $attributes
 */
function tasks_handle_on_task_updated(Task $task, array $attributes)
{
    $event_type = 'TaskUpdated';

    // If there is attribute key task_list_id set the event type for webhook as TaskListChanged
    if (!empty($attributes['task_list_changed'])) {
        $event_type = 'TaskListChanged';
    } else {
        // If any of the attributes is related to task completion, set the event type for webhooks as TaskCompleted
        foreach (['completed_by_id', 'completed_by_name', 'completed_by_email', 'completed_on'] as $attribute) {
            if (array_key_exists($attribute, $attributes)) {
                $event_type = 'TaskCompleted';
                break;
            }
        }
    }

    Webhooks::dispatch($task, $event_type);
}
