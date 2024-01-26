<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Invoice generated and issued via recurring profile notification.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage notification
 */
class InvoiceGeneratedViaRecurringProfileNotification extends RecurringProfileNotification
{
    /**
     * Set invoice.
     *
     * @param  Invoice                      $invoice
     * @return RecurringProfileNotification
     */
    public function &setInvoice(Invoice $invoice)
    {
        $this->setAdditionalProperty('invoice_id', $invoice->getId());

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
        return ['profile' => $this->getProfile(), 'invoice_recipients' => $this->getInvoice()->getRecipientInstances()];
    }

    /**
     * Return invoice.
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return DataObjectPool::get('Invoice', $this->getAdditionalProperty('invoice_id'));
    }
}
