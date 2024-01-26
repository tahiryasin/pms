<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Authorize.net integration.
 *
 * @package angie.framework.payments
 * @subpackage integrations
 */
class AuthorizenetIntegration extends CreditCardIntegration
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
        return 'AuthorizeNet';
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return 'authorize-net';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return lang('Credit card processing service');
    }

    /**
     * Returns true if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        if ($gateway = Payments::getCreditCardGateway()) {
            return $gateway instanceof AuthorizeGateway && $gateway->getIsEnabled();
        }

        return false;
    }
}
