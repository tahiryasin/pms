<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * SupportExpirationSystemNotification system notification manager.
 *
 * @package angie.framework.environment
 * @subpackage models
 */
class SupportExpirationSystemNotifications extends SystemNotifications
{
    /**
     * Return 'type' attribute for polymorh model creation.
     *
     * @return string
     */
    public static function getType()
    {
        return 'SupportExpirationSystemNotification';
    }

    /**
     * Return true if this notification should ne raised.
     *
     * @return bool
     */
    public static function shouldBeRaised()
    {
        $expires_on = DateValue::makeFromTimestamp(AngieApplication::autoUpgrade()->getSupportSubscriptionExpiresOn());

        if (empty($expires_on)) {
            $expires_on = new DateValue();
        }

        $today = new DateValue();
        $days_between = $today->daysBetween($expires_on);

        return in_array($days_between, [30, 15, 7, 3, 2, 1]);
    }
}
