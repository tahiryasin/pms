<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Send invoice notification.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage notifications
 */
class SendInvoiceNotification extends InvoiceNotification
{
    /**
     * Set custom subject.
     *
     * @param  string $value
     * @return $this
     */
    public function &setCustomSubject($value)
    {
        $this->setAdditionalProperty('custom_subject', $value);

        return $this;
    }

    /**
     * Set custom message.
     *
     * @param  string $value
     * @return $this
     */
    public function &setCustomMessage($value)
    {
        $this->setAdditionalProperty('custom_message', $value);

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
        return array_merge(parent::getAdditionalTemplateVars($channel), ['custom_subject' => $this->getCustomSubject(), 'custom_message' => $this->getCustomMessage()]);
    }

    /**
     * Get custom subject.
     *
     * @return string
     */
    public function getCustomSubject()
    {
        return $this->getAdditionalProperty('custom_subject');
    }

    /**
     * Get custom message.
     *
     * @return string
     */
    public function getCustomMessage()
    {
        return $this->getAdditionalProperty('custom_message');
    }

    // ---------------------------------------------------
    //  Delivery
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
