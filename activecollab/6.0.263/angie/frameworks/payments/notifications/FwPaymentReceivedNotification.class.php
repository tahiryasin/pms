<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level payment received notification instance.
 *
 * @package angie.frameworks.payments
 * @subpackage models
 */
abstract class FwPaymentReceivedNotification extends Notification
{
    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        $result = ['parent' => $this->getParent()];

        return $result;
    }

    /**
     * Check if notifaciton should be displayed in a specific channel.
     *
     * @param  NotificationChannel $channel
     * @param  IUser               $recipient
     * @return bool
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return true; // Comment notifiactions should always go to email
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }
}
