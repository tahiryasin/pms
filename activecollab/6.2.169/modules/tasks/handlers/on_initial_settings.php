<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Handle on_initial_settings event handler.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * @param array $settings
 */
function tasks_handle_on_initial_settings(array &$settings)
{
    $settings['show_project_id'] = ConfigOptions::getValue('show_project_id');
    $settings['show_task_id'] = ConfigOptions::getValue('show_task_id');
    $settings['task_estimates_enabled'] = ConfigOptions::getValue('task_estimates_enabled');
    $settings['task_estimates_enabled_lock'] = ConfigOptions::getValue('task_estimates_enabled_lock');
    $settings['show_task_estimates_to_clients'] = ConfigOptions::getValue('show_task_estimates_to_clients');
}
