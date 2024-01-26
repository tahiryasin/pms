<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddCrispStatus extends AngieModelMigration
{
    public function up()
    {
        if (AngieApplication::isOnDemand()) {
            if (!$this->getConfigOptionValue('live_chat_state')) {
                $this->addConfigOption('live_chat_state', 'enabled');
            }

            if (!$this->getConfigOptionValue('live_chat_notification_for_existing_users_state')) {
                $this->addConfigOption('live_chat_notification_for_existing_users_state', 'dismissed');
            }

            if (!$this->getConfigOptionValue('live_chat_notification_for_new_users_state')) {
                $this->addConfigOption('live_chat_notification_for_new_users_state', 'disabled');
            }

            /** @var User[] $users */
            $users = Users::find();

            if (!empty($users)) {
                foreach ($users as $user) {
                    if (!$user->isClient()) {
                        if (!ConfigOptions::hasValueFor('live_chat_state', $user)) {
                            ConfigOptions::setValueFor('live_chat_state', $user, 'disabled');
                        }

                        if (!ConfigOptions::hasValueFor('live_chat_notification_for_existing_users_state', $user)) {
                            ConfigOptions::setValueFor('live_chat_notification_for_existing_users_state', $user, 'enabled');
                        }

                        if (!ConfigOptions::hasValueFor('live_chat_notification_for_new_users_state', $user)) {
                            ConfigOptions::setValueFor('live_chat_notification_for_new_users_state', $user, 'dismissed');
                        }
                    }
                }
            }
        }
    }
}
