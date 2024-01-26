<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

abstract class SystemNotifications extends FwSystemNotifications
{
    public static function toggle()
    {
        if (AngieApplication::isOnDemand()) {
            if (DiskSpaceSystemNotifications::shouldBeRaised()) {
                DiskSpaceSystemNotifications::add();
            } else {
                DiskSpaceSystemNotifications::clearNotifications();
            }

            if (FreeTrialSystemNotifications::shouldBeRaised()) {
                FreeTrialSystemNotifications::add();
            } else {
                FreeTrialSystemNotifications::clearNotifications();
            }

            if (MembersExceededSystemNotifications::shouldBeRaised()) {
                MembersExceededSystemNotifications::add();
            } else {
                MembersExceededSystemNotifications::clearNotifications();
            }

            if (PaymentFailedSystemNotifications::shouldBeRaised()) {
                PaymentFailedSystemNotifications::add();
            } else {
                PaymentFailedSystemNotifications::clearNotifications();
            }

            if (SubscriptionCancelledSystemNotifications::shouldBeRaised()) {
                SubscriptionCancelledSystemNotifications::add();
            } else {
                SubscriptionCancelledSystemNotifications::clearNotifications();
            }
        } else {
            if (SupportExpirationSystemNotifications::shouldBeRaised()) {
                SupportExpirationSystemNotifications::add();
            } else {
                SupportExpirationSystemNotifications::clearNotifications();
            }

            if (UpgradeAvailableSystemNotifications::shouldBeRaised()) {
                UpgradeAvailableSystemNotifications::add();
            } else {
                UpgradeAvailableSystemNotifications::clearNotifications();
            }
        }
    }
}
