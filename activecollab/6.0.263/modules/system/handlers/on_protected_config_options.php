<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_protected_config_options event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_protected_config_options event.
 */
function system_handle_on_protected_config_options()
{
    ConfigOptions::protect(['morning_paper_last_activity'], function () {
        return false;
    }, function () {
        return false;
    });

    // Password policy settings can be read by anyone, but managed by owners only.
    ConfigOptions::protect(['password_policy_min_length', 'password_policy_require_numbers', 'password_policy_require_mixed_case', 'password_policy_require_symbols'], function (User $user) {
        return true;
    }, function (User $user) {
        return $user->isOwner();
    });

    ConfigOptions::protect(['maintenance_enabled'], function (User $user) {
        return $user->isOwner();
    }, function (User $user) {
        return $user->isOwner();
    });
}