<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * New subtask notification.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage notifications
 */
class NewSubtaskNotification extends BaseSubtaskNotification
{
    /**
     * Set update flags for combined object updates collection.
     *
     * @param array $updates
     */
    public function onObjectUpdateFlags(array &$updates)
    {
        if (empty($updates['new_subtask'])) {
            $updates['new_subtask'] = 1;
        } else {
            ++$updates['new_subtask'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function optOutConfigurationOptions(NotificationChannel $channel = null)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return array_merge(parent::optOutConfigurationOptions($channel), ['notifications_user_send_email_assignments']);
        }

        return parent::optOutConfigurationOptions($channel);
    }
}
