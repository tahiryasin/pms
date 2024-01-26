<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Base invoice notification.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage notifications
 */
abstract class InvoiceNotification extends Notification
{
    /**
     * Return visit URL.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getVisitUrl(IUser $user)
    {
        $parent = $this->getParent();

        return $parent instanceof Invoice ? $parent->getPublicUrl() : '#';
    }

    /**
     * Return files attached to this notification, if any.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAttachments(NotificationChannel $channel)
    {
        $parent = $this->getParent();

        return $parent instanceof Invoice ? [$parent->exportToFile() => Invoices::getInvoicePdfName($parent)] : null;
    }

    /**
     * Return true if sender should be ignored.
     *
     * @return bool
     */
    public function ignoreSender()
    {
        return false;
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        return ['owner_company' => Companies::findOwnerCompany()];
    }

    /**
     * This notification should not be displayed in web interface.
     *
     * @param  NotificationChannel $channel
     * @param  IUser               $recipient
     * @return bool
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($channel instanceof WebInterfaceNotificationChannel) {
            return false; // Users should see no notifications about invoices in the web interface
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }
}
