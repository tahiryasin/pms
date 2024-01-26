<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Stripe integration implementation.
 *
 * @package angie.framework.payments
 * @subpackage integrations
 */
class StripeIntegration extends CreditCardIntegration
{
    /**
     * @return bool
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Stripe';
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return 'stripe';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return lang('Web and mobile payments');
    }

    /**
     * Returns true if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        if ($gateway = Payments::getCreditCardGateway()) {
            return $gateway instanceof StripeGateway && $gateway->getIsEnabled();
        }

        return false;
    }
}
