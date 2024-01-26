<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Morning paper notification.
 *
 * @package ActiveCollab.modules.system
 * @subpackage notifications
 */
class MorningPaperNotification extends Notification
{
    /**
     * Set paper day.
     *
     * @param  DateValue                $day
     * @return MorningPaperNotification
     */
    public function &setPaperDay(DateValue $day)
    {
        $this->setAdditionalProperty('day', $day);

        return $this;
    }

    /**
     * Set previous business day.
     *
     * @param  DateValue                $day
     * @return MorningPaperNotification
     */
    public function &setPreviousDay(DateValue $day)
    {
        $this->setAdditionalProperty('previous_day', $day);

        return $this;
    }

    /**
     * Set paper data for a given user.
     *
     * @param  array|null               $prev_data
     * @param  array|null               $today_data
     * @param  array|null               $late_data
     * @return MorningPaperNotification
     */
    public function &setPaperData($prev_data, $today_data, $late_data)
    {
        $this->setAdditionalProperty('prev_data', $prev_data);
        $this->setAdditionalProperty('today_data', $today_data);
        $this->setAdditionalProperty('late_data', $late_data);

        return $this;
    }

    /**
     * Return true if this notification should be visible in a given notificaiton channel.
     *
     * @param  NotificationChannel $channel
     * @param  IUser               $recipient
     * @return bool
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof WebInterfaceNotificationChannel) {
            return false; // Never show in web interface
        }

        if ($channel instanceof EmailNotificationChannel) {
            return true; // Always send an email
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }

    /**
     * Return subscription code.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getSubscriptionCode(IUser $user)
    {
        return $user instanceof User ? MorningPaper::getSubscriptionCode($user) : null;
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return [
                'paper_day' => $this->getPaperDay(),
                'previous_day' => DateValue::makeFromString($this->getPreviousDay()),
                'prev_data' => $this->getAdditionalProperty('prev_data'),
                'today_data' => $this->getAdditionalProperty('today_data'),
                'late_data' => $this->getAdditionalProperty('late_data'),
            ];
        }

        return parent::getAdditionalProperties($channel);
    }

    /**
     * Return paper day.
     *
     * @return DateValue
     */
    public function getPaperDay()
    {
        return DateValue::makeFromString($this->getAdditionalProperty('day'));
    }

    /**
     * Return previous business day.
     *
     * @return DateValue
     */
    public function getPreviousDay()
    {
        return DateValue::makeFromString($this->getAdditionalProperty('previous_day'));
    }
}
