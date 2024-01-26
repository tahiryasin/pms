<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Notifications;

use Angie\Mailer\Decorator\Decorator;
use ApplicationObject;
use IUser;
use Notification;
use NotificationChannel;

interface NotificationsInterface
{
    /**
     * Create a notification about given event within a given context.
     *
     * @param  string            $event
     * @param  ApplicationObject $context
     * @param  IUser             $sender
     * @param  Decorator         $decorator
     * @return Notification
     */
    public function notifyAbout($event, $context = null, $sender = null, $decorator = null);

    /**
     * Return notification template path.
     *
     * @param  Notification               $notification
     * @param  NotificationChannel|string $channel
     * @return string
     */
    public function getNotificationTemplatePath(Notification $notification, $channel);

    /**
     * Send $notification to the list of recipients.
     *
     * @param Notification $notification
     * @param IUser[]      $users
     * @param bool         $skip_sending_queue
     */
    public function sendNotificationToRecipients(Notification &$notification, $users, $skip_sending_queue = false);

    /**
     * Return notification channels.
     *
     * @return NotificationChannel[]
     */
    public function getChannels();

    /**
     * Returns true if channels are open.
     *
     * @return bool
     */
    public function channelsAreOpen();

    /**
     * Open notifications channels for bulk sending.
     */
    public function openChannels();

    /**
     * Close notification channels for bulk sending.
     *
     * @param bool $sending_interupted
     */
    public function closeChannels($sending_interupted = false);
}
