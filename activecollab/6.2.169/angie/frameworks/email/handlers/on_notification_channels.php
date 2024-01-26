<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_notification_channels event handler.
 *
 * @package angie.frameworks.email
 * @subpackage email
 */

/**
 * Register email notification channel.
 *
 * @param NotificationChannel[] $channels
 */
function email_handle_on_notification_channels(&$channels)
{
    $channels[] = new EmailNotificationChannel();
}
