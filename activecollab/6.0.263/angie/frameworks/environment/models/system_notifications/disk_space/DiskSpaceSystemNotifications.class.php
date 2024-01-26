<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Disk space system notification.
 *
 * @package angie.environment
 * @subpackage models
 */
class DiskSpaceSystemNotifications extends SystemNotifications
{
    public static function getType()
    {
        return DiskSpaceSystemNotification::class;
    }

    /**
     * Return true if this notification should ne raised.
     *
     * @return bool
     */
    public static function shouldBeRaised()
    {
        return AngieApplication::isOnDemand() && AngieApplication::storage()->isDiskFull(true);
    }
}
