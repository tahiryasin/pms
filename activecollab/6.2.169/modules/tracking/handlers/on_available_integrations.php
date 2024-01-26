<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_available_integrations event handler.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage handlers
 */

/**
 * Handle on_available_integrations event.
 *
 * @param array $integrations
 * @param User  $user
 */
function tracking_handle_on_available_integrations(array &$integrations, User &$user)
{
    if (!($user instanceof Client)) {
        $integrations[] = Integrations::findFirstByType('TimerIntegration');
    }
}
