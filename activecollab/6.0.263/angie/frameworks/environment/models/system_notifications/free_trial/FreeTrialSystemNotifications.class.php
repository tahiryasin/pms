<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Free Trial system notification.
 *
 * @package angie.environment
 * @subpackage models
 */
class FreeTrialSystemNotifications extends SystemNotifications
{
    /**
     * Return 'type' attribute for polymorh model creation.
     *
     * @return mixed|string
     */
    public static function getType()
    {
        return 'FreeTrialSystemNotification';
    }

    /**
     * Return true if this notification should ne raised.
     *
     * @return bool
     */
    public static function shouldBeRaised()
    {
        $account_status = AngieApplication::accountSettings()->getAccountStatus();

        if (AngieApplication::isOnDemand() && $account_status->isTrial()) {
            if ($account_status->getDaysToStatusExpiration() <= 4) {
                self::clearNotifications(); // remove notifications of this type and show the new ones

                return true;
            }

            return false;
        }

        return false;
    }
}
