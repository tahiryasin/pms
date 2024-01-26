<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * PayPal Direct integration.
 *
 * @package angie.framework.payments
 * @subpackage integrations
 */
class PaypalDirectIntegration extends CreditCardIntegration
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
        return 'PayPal Direct Payments';
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return 'paypal';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return lang('Receive credit card payments (requires PayFlow Pro)');
    }

    /**
     * Returns true if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        if ($gateway = Payments::getCreditCardGateway()) {
            return $gateway instanceof PaypalDirectGateway && $gateway->getIsEnabled();
        }

        return false;
    }
}
