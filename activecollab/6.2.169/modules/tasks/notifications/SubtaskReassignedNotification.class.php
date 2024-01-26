<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Subtask reassigned notification.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage notifications
 */
class SubtaskReassignedNotification extends BaseSubtaskNotification
{
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
