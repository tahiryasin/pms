<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Base recuring profile notification.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage notifications
 */
abstract class RecurringProfileNotification extends Notification
{
    /**
     * Set parent profile.
     *
     * @param  RecurringProfile             $profile
     * @return RecurringProfileNotification
     */
    public function &setProfile(RecurringProfile $profile)
    {
        $this->setAdditionalProperty('profile_id', $profile->getId());

        return $this;
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        return [
            'profile' => $this->getProfile(),
        ];
    }

    /**
     * Return parent recurring profile.
     *
     * @return RecurringProfile|DataObject
     */
    public function getProfile()
    {
        return DataObjectPool::get(RecurringProfile::class, $this->getAdditionalProperty('profile_id'));
    }

    /**
     * Return files attached to this notification, if any.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAttachments(NotificationChannel $channel)
    {
        /** @var Invoice $parent */
        if ($parent = $this->getParent()) {
            return [$parent->exportToFile() => 'invoice.pdf'];
        }

        return null;
    }

    /**
     * This notification should not be displayed in web interface.
     *
     * @param  NotificationChannel $channel
     * @param  IUser               $recipient
     * @return bool
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return true; // Always deliver this notification via email
        } elseif ($channel instanceof WebInterfaceNotificationChannel) {
            return false; // Never deliver this notification to web interface
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }
}
