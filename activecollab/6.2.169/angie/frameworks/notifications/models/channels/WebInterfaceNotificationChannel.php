<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Web interface notification channel.
 *
 * @package angie.frameworks.notifications
 * @subpackage models
 */
class WebInterfaceNotificationChannel extends NotificationChannel
{
    const CHANNEL_NAME = 'web_interface';

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
        return 'Web Interface Notifications';
    }

    /**
     * Returns true if this channel is enabled by default.
     *
     * @return bool
     */
    public function isEnabledByDefault()
    {
        return true; // Always enabled
    }

    /**
     * Returns true if this channel is enabled for this user.
     *
     * @param  User $user
     * @return bool
     */
    public function isEnabledFor(User $user)
    {
        return true; // Always enabled
    }

    // ---------------------------------------------------
    //  Open / Close
    // ---------------------------------------------------

    /**
     * Open channel for sending.
     */
    public function open()
    {
        DB::beginWork('Saving notifications for recipients @ ' . __CLASS__);
        parent::open();
    }

    /**
     * Close channel after notifications have been sent.
     *
     * @param bool $sending_interupted
     */
    public function close($sending_interupted = false)
    {
        if ($sending_interupted) {
            DB::rollback('Failed to save notifications for recipients @ ' . __CLASS__);
        } else {
            DB::commit('Notifications saved for recipients @ ' . __CLASS__);
        }

        parent::close($sending_interupted);
    }

    /**
     * Send notification via this channel.
     *
     * @param Notification $notification
     * @param bool         $skip_sending_queue
     * @param IUser        $recipient
     */
    public function send(Notification &$notification, IUser $recipient, $skip_sending_queue = false)
    {
        if ($recipient instanceof User) {
            $notification->addRecipient($recipient);
        }
    }
}
