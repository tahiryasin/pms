<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Trait that implements new project element email notification opt out config.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
trait INewProjectElementNotificationOptOutConfig
{
    /**
     * {@inheritdoc}
     */
    public function optOutConfigurationOptions(NotificationChannel $channel = null)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return array_merge(parent::optOutConfigurationOptions($channel), ['notifications_user_send_email_new_project_element']);
        }

        return parent::optOutConfigurationOptions($channel);
    }
}
