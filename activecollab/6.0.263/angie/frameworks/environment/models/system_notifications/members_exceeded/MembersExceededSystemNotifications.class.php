<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * MembersExceededSystemNotifications system notification.
 *
 * @package angie.environment
 * @subpackage models
 */
class MembersExceededSystemNotifications extends SystemNotifications
{
    /**
     * Return 'type' attribute for polymorh model creation.
     *
     * @return string
     */
    public static function getType()
    {
        return 'MembersExceededSystemNotification';
    }

    /**
     * Return true if this notification should ne raised.
     *
     * @return bool
     */
    public static function shouldBeRaised()
    {
        $max_members = AngieApplication::accountSettings()->getAccountPlan()->getMaxMembers();

        if (empty($max_members)) {
            return false;
        }

        return AngieApplication::isOnDemand() && Users::countActiveUsers() > $max_members;
    }
}
