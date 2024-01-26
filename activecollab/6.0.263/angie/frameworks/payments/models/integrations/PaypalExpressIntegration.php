<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Paypal Express Checkout integration.
 *
 * @package angie.framework.payments
 * @subpackage integrations
 */
class PaypalExpressIntegration extends Integration
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
        return 'PayPal Express Checkout';
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return 'paypal-express';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return lang('Accept PayPal payments');
    }

    /**
     * Get group of this integration.
     *
     * @return string
     */
    public function getGroup()
    {
        return 'payment_processing';
    }

    /**
     * Returns true if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        if ($gateway = Payments::getPayPalGateway()) {
            return $gateway instanceof PaypalExpressCheckoutGateway && $gateway->getIsEnabled();
        }

        return false;
    }
}
