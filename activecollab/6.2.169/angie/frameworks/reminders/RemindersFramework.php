<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class RemindersFramework extends AngieFramework
{
    const NAME = 'reminders';

    protected $name = 'reminders';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            [
                Reminder::class,
                CustomReminder::class,
            ],
            function ($ids) {
                return Reminders::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        AngieApplication::setForAutoload(
            [
                IReminders::class => __DIR__ . '/models/IReminders.php',
                IRemindersImplementation::class => __DIR__ . '/models/IRemindersImplementation.php',

                FwReminder::class => __DIR__ . '/models/reminders/FwReminder.php',
                FwReminders::class => __DIR__ . '/models/reminders/FwReminders.php',

                FwCustomReminder::class => __DIR__ . '/models/FwCustomReminder.php',
                FwCustomReminderNotification::class => __DIR__ . '/notifications/FwCustomReminderNotification.php',
            ]
        );
    }

    public function defineHandlers()
    {
        $this->listen('on_morning_mail');
        $this->listen('on_notification_inspector');
    }
}
