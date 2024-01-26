<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class StorageOverusedNotification extends Notification
{
    /**
     * Get allowed disk space.
     *
     * @return mixed
     */
    public function getDiskSpaceLimit()
    {
        return $this->getAdditionalProperty('disk_space_limit');
    }

    /**
     * Set allowed disk space.
     *
     * @param  string                      $disk_space_limit
     * @return StorageOverusedNotification
     */
    public function &setDiskSpaceLimit($disk_space_limit)
    {
        $this->setAdditionalProperty('disk_space_limit', $disk_space_limit);

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
            'disk_space_limit' => $this->getDiskSpaceLimit(),
        ];
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
