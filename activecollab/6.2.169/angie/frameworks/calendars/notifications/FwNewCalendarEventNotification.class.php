<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * New calendar event notification.
 *
 * @package angie.frameworks.calendars
 * @subpackage notifications
 */
abstract class FwNewCalendarEventNotification extends Notification
{
    use INewInstanceUpdate;

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        if ($channel instanceof EmailNotificationChannel) {
            /** @var CalendarEvent $event */
            if ($event = $this->getParent()) {
                return [
                    'starts_on' => $event->getStartsOn(),
                    'starts_on_time' => $event->getStartsOnTime(),
                    'calendar' => $event->getCalendar(),
                ];
            }
        }

        return parent::getAdditionalTemplateVars($channel);
    }
}
