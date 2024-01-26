<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * New project notification.
 *
 * @package ActiveCollab.modules.system
 * @subpackage notifications
 */
class NewProjectNotification extends Notification
{
    use INewInstanceUpdate;

    /**
     * {@inheritdoc}
     */
    public function supportsGoToAction(IUser $recipient)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function optOutConfigurationOptions(NotificationChannel $channel = null)
    {
        $result = parent::optOutConfigurationOptions($channel);

        if ($channel instanceof EmailNotificationChannel) {
            $result[] = 'notifications_user_send_email_assignments';
        }

        return $result;
    }
}
