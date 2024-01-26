<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Custom reminder notification.
 *
 * @package angie.frameworks.reminders
 * @subpackage notification
 */
abstract class FwCustomReminderNotification extends Notification
{
    /**
     * Return reminder instance.
     *
     * @return Reminder|DataObject
     */
    public function getReminder()
    {
        return DataObjectPool::get(Reminder::class, $this->getAdditionalProperty('reminder_id'));
    }

    /**
     * Set reminder instance.
     *
     * @param  Reminder                         $reminder
     * @return CustomReminderNotification|$this
     */
    public function &setReminder(Reminder $reminder)
    {
        $this->setAdditionalProperty('reminder_id', $reminder->getId());

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
        return ['reminder' => $this->getReminder()];
    }

    /**
     * Return true if sender should be ignored.
     *
     * @return bool
     */
    public function ignoreSender()
    {
        return false;
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
