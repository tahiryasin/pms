<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Invoice reminder notification.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage notifications
 */
class InvoiceReminderNotification extends InvoiceNotification
{
    /**
     * Set reminder message.
     *
     * @param  string                      $value
     * @return InvoiceReminderNotification
     */
    public function &setReminderMessage($value)
    {
        $this->setAdditionalProperty('reminder_message', $value);

        return $this;
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        return array_merge(parent::getAdditionalTemplateVars($channel), [
            'additional_message' => $this->getReminderMessage(),
            'overdue_days' => $this->getParent()->getDueOn()->daysBetween(DateTimeValue::now()),
        ]);
    }

    /**
     * Get reminder message.
     *
     * @return string
     */
    public function getReminderMessage()
    {
        return $this->getAdditionalProperty('reminder_message');
    }

    // ---------------------------------------------------
    //  Delivery system
    // ---------------------------------------------------

    /**
     * This notification should not be displayed in web interface.
     *
     * @param  NotificationChannel $channel
     * @param  IUser               $recipient
     * @return bool
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return true; // Always deliver notifications via email
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }
}
