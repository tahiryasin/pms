<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_protected_config_options event handler.
 *
 * @package activeCollab.modules.tracking
 * @subpackage handlers
 */

/**
 * Handle on_protected_config_options.
 */
function tracking_handle_on_protected_config_options()
{
    ConfigOptions::protect([
        'default_billable_status',
        'default_project_budget_type',
        'default_tracking_objects_are_billable',
        'default_members_can_change_billable',
    ], function (User $user) {
        return $user->isOwner();
    }, function (User $user) {
        return $user->isOwner();
    });
}