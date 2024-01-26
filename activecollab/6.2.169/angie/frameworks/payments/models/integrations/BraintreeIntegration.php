<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Braintree integration.
 *
 * @package angie.framework.payments
 * @subpackage integrations
 */
class BraintreeIntegration extends CreditCardIntegration
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
        return 'Braintree';
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return 'braintree';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return lang('Accept PayPal, Bitcoin, Apple Pay, and credit cards');
    }

    /**
     * Returns true if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        if ($gateway = Payments::getCreditCardGateway()) {
            return $gateway instanceof BrainTreeGateway && $gateway->getIsEnabled();
        }

        return false;
    }
}
