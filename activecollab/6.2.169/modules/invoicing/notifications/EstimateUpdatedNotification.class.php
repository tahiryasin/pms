<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Estimate updated notification.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage notifications
 */
class EstimateUpdatedNotification extends EstimateNotification
{
    /**
     * Set old total.
     *
     * @param  float $value
     * @return $this
     */
    public function &setOldTotal($value)
    {
        $this->setAdditionalProperty('old_total', $value);

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
        return array_merge(parent::getAdditionalTemplateVars($channel), ['old_total' => $this->getOldTotal()]);
    }

    /**
     * Get old total.
     *
     * @return float
     */
    public function getOldTotal()
    {
        return $this->getAdditionalProperty('old_total');
    }

    /**
     * Return attachments.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAttachments(NotificationChannel $channel)
    {
        if ($channel instanceof EmailNotificationChannel) {
            $parent = $this->getParent();

            if ($parent instanceof Estimate) {
                return [$parent->exportToFile() => Estimates::getEstimatePdfName($parent)];
            }
        }

        return parent::getAttachments($channel);
    }

    /**
     * If sender is added to the list of recipients, they should receive the email.
     *
     * @return bool
     */
    public function ignoreSender()
    {
        return false;
    }
}
