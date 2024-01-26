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
    ConfigOptions::protect(['default_billable_status'], function (User $user) {
        return true;
    }, function (User $user) {
        return $user->isOwner();
    });
}
