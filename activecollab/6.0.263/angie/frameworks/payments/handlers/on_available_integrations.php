<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_available_integrations event handler.
 *
 * @package angie.frameworks.payments
 * @subpackage handlers
 */

/**
 * Handle on_available_integrations event.
 *
 * @param array $integrations
 * @param User  $user
 */
function payments_handle_on_available_integrations(array &$integrations, User &$user)
{
    if ($user instanceof Owner) {
        $integrations[] = Integrations::findFirstByType(AuthorizenetIntegration::class);
        $integrations[] = Integrations::findFirstByType(StripeIntegration::class);
        $integrations[] = Integrations::findFirstByType(BraintreeIntegration::class);
        $integrations[] = Integrations::findFirstByType(PaypalDirectIntegration::class);
        $integrations[] = Integrations::findFirstByType(PaypalExpressIntegration::class);
    }
}
