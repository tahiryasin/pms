<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_available_integrations event handler.
 *
 * @package angie.frameworks.environment
 * @subpackage handlers
 */

/**
 * Handle on_available_integrations event.
 *
 * @param array $integrations
 * @param User  $user
 */
function environment_handle_on_available_integrations(array &$integrations, User &$user)
{
    if ($user instanceof Owner) {
        $integrations[] = new WebhooksIntegration();
    }
}
