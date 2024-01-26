<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_protected_config_options event handler.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * Handle on_protected_config_options event.
 */
function tasks_handle_on_protected_config_options()
{
    ConfigOptions::protect(['show_project_id', 'show_task_id'], function (User $user) {
        return true;
    }, function (User $user) {
        return $user->isOwner();
    });

    ConfigOptions::protect(['task_estimates_enabled_lock'], function (User $user) {
        return true;
    }, function (User $user) {
        return false;
    });

    ConfigOptions::protect(['task_estimates_enabled'], function (User $user) {
        return true;
    }, function (User $user) {
        $is_locked = ConfigOptions::getValue('task_estimates_enabled_lock');

        return !$is_locked && $user->isOwner();
    });
}
