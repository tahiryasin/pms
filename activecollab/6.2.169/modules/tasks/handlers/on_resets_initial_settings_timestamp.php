<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_resets_initial_settings_timestamp event handler.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * @param array $config_options
 */
function tasks_handle_on_resets_initial_settings_timestamp(array &$config_options)
{
    $config_options[] = 'show_project_id';
    $config_options[] = 'show_task_id';
    $config_options[] = 'task_estimates_enabled';
    $config_options[] = 'task_estimates_enabled_lock';
}
