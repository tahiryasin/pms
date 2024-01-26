<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Pusher\Pusher;

/**
 * Real-Time notification channel.
 *
 * @package angie.frameworks.notifications
 * @subpackage models
 */
class RealTimeNotificationChannel extends NotificationChannel
{
    const CHANNEL_NAME = 'real_time';

    /**
     * Return channel short name.
     *
     * @return string
     */
    public function getShortName()
    {
        return self::CHANNEL_NAME;
    }

    /**
     * Return verbose name of the channel.
     *
     * @return string
     */
    public function getVerboseName()
    {
        return lang('Real-Time Notifications');
    }

    /**
     * Returns true if this channel is enabled by default.
     *
     * @return bool
     */
    public function isEnabledByDefault()
    {
        return true;
    }

    /**
     * Returns true if this channel is enabled for this user.
     *
     * @param  User $user
     * @return bool
     */
    public function isEnabledFor(User $user)
    {
        return true;
    }

    /**
     * Send notification via this channel.
     *
     * @param  Notification $notification
     * @param  IUser        $recipient
     * @param  bool         $skip_sending_queue
     * @throws Exception
     */
    public function send(Notification &$notification, IUser $recipient, $skip_sending_queue = false)
    {
        if (defined('PUSHER_APP_KEY') && defined('PUSHER_APP_SECRET') && defined('PUSHER_APP_ID')) {
            $options = [
                'encrypted' => true,
            ];

            try {
                $pusher = new Pusher(PUSHER_APP_KEY, PUSHER_APP_SECRET, PUSHER_APP_ID, $options);
                $pusher->trigger(["user-{$recipient->getId()}"], $notification->getShortName(), [
                    'pusher_notification' => true,
                ]);
            } catch (Exception $e) {
                AngieApplication::log()->error($e->getMessage());
            }
        }
    }
}
