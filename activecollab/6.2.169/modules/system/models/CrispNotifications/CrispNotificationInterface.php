<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

interface CrispNotificationInterface
{
    const NOTIFICATION_STATUS_DISABLED = 'disabled';
    const NOTIFICATION_STATUS_ENABLED = 'enabled';
    const NOTIFICATION_STATUS_DISMISSED = 'dismissed';

    const LIVE_CHAT_NOTIFICATION_FOR_EXISTING_USERS = 'live_chat_notification_for_existing_users_state';
    const LIVE_CHAT_NOTIFICATION_FOR_NEW_USERS = 'live_chat_notification_for_new_users_state';

    const NOTIFICATION_STATUSES = [
        self::NOTIFICATION_STATUS_DISABLED,
        self::NOTIFICATION_STATUS_DISMISSED,
        self::NOTIFICATION_STATUS_ENABLED,
    ];

    const LIVE_CHAT_NOTIFICATIONS = [
        self::LIVE_CHAT_NOTIFICATION_FOR_NEW_USERS,
        self::LIVE_CHAT_NOTIFICATION_FOR_EXISTING_USERS,
    ];

    public function enable();

    public function dismiss();

    public function getState();
}
