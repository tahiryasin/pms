<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_available_integrations event handler.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage handlers
 */

/**
 * Handle on_available_integrations event.
 *
 * @param array $integrations
 * @param User  $user
 */
function invoicing_handle_on_available_integrations(array &$integrations, User &$user)
{
    if ($user instanceof Owner || $user->isFinancialManager()) {
        $integrations[] = Integrations::findFirstByType(QuickbooksIntegration::class);
        $integrations[] = Integrations::findFirstByType(XeroIntegration::class);
    }
}
