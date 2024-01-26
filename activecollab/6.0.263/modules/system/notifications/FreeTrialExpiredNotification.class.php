<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class FreeTrialExpiredNotification extends Notification
{
    public function &setSubject($subject)
    {
        $this->setAdditionalProperty('subject', $subject);

        return $this;
    }

    public function getSubject()
    {
        return $this->getAdditionalProperty('subject');
    }

    public function &setPayload($payload)
    {
        $this->setAdditionalProperty('payload', $payload);

        return $this;
    }

    public function getPayload()
    {
        return $this->getAdditionalProperty('payload');
    }

    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        return [
            'payload' => $this->getPayload(),
            'subject' => $this->getSubject(),
        ];
    }

    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return true;
        } elseif ($channel instanceof WebInterfaceNotificationChannel) {
            return false;
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }
}
