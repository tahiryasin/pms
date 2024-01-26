<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * UpgradeAvailableSystemNotifications system notification.
 *
 * @package angie.environment
 * @subpackage models
 */
class UpgradeAvailableSystemNotifications extends SystemNotifications
{
    /**
     * Return 'type' attribute for polymorh model creation.
     *
     * @return mixed|string
     */
    public static function getType()
    {
        return 'UpgradeAvailableSystemNotification';
    }

    /**
     * Return true if this notification should ne raised.
     *
     * @return bool
     */
    public static function shouldBeRaised()
    {
        if (!AngieApplication::isOnDemand()) {
            return AngieApplication::getVersion() != AngieApplication::autoUpgrade()->getLatestAvailableVersion();
        }

        return false;
    }
}
