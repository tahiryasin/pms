<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * PaymentFailedSystemNotifications system notification.
 *
 * @package angie.environment
 * @subpackage models
 */
class PaymentFailedSystemNotifications extends SystemNotifications
{
    /**
     * Return 'type' attribute for polymorh model creation.
     *
     * @return string
     */
    public static function getType()
    {
        return 'PaymentFailedSystemNotification';
    }

    /**
     * Return true if this notification should ne raised.
     *
     * @return mixed|void
     */
    public static function shouldBeRaised()
    {
        if (AngieApplication::accountSettings()->getAccountStatus()->isFailedPayment()) {
            self::clearNotifications(); // remove notifications of this type and show the new ones

            return true;
        }

        return false;
    }

    public static function getAccountDaysUntilSuspension()
    {
        return (int) AngieApplication::accountSettings()->getAccountStatus()->getDaysToSuspension();
    }
}
