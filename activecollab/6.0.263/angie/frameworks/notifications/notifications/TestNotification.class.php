<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Test notification class.
 *
 * @package angie.frameworks.notifications
 * @subpackage models
 */
class TestNotification extends Notification
{
    /**
     * {@inheritdoc}
     */
    public function isUserMentioned($user)
    {
        if ($user instanceof IUser) {
            return $user->getEmail() == 'email@a51dev.com' ? true : parent::isUserMentioned($user);
        } else {
            throw new InvalidInstanceError('user', $user, 'IUser');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function optOutConfigurationOptions(NotificationChannel $channel = null)
    {
        return array_merge(parent::optOutConfigurationOptions($channel), ['notification_accept_test_1', 'notification_accept_test_2']);
    }
}
