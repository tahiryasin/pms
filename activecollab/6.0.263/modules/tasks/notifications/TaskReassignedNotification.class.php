<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Task reassigned notification.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage notifications
 */
class TaskReassignedNotification extends Notification
{
    /**
     * {@inheritdoc}
     */
    public function onObjectUpdateFlags(array &$updates)
    {
        if (empty($updates['reassigned'])) {
            $updates['reassigned'] = 1;
        } else {
            ++$updates['reassigned'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
        /** @var Task $parent */
        if ($parent = $this->getParent()) {
            if (empty($type_ids_map['Project'])) {
                $type_ids_map['Project'] = [$parent->getProjectId()];
            } else {
                if (!in_array($parent->getProjectId(), $type_ids_map['Project'])) {
                    $type_ids_map['Project'][] = $parent->getProjectId();
                }
            }
        }
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
