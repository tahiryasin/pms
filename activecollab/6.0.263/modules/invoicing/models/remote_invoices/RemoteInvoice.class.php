<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * RemoteInvoice class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class RemoteInvoice extends BaseRemoteInvoice
{
    // Primary statuses
    const UNSENT = 'unsent';
    const PAID = 'paid';
    const SENT = 'sent';
    const PAID_AND_CANCELED = 'paid_and_canceled';
    const PARTIALLY_PAID = 'partially_paid';

    /**
     * {@inheritdoc}
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->setType(get_class($this));
    }

    /**
     * Set items.
     *
     * @param  array $items
     * @return array
     */
    public function setItems(array $items)
    {
        return $this->setAdditionalProperty('items', $items);
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        return array_merge($result, [
            'client' => $this->getClient(),
            'remote_code' => $this->getRemoteCode(),
            'amount' => $this->getAmount(),
            'number' => $this->getInvoiceNumber(),
            'balance' => $this->getBalance(),
            'items' => $this->getItems(),
            'is_paid' => $this->isPaid(),
        ]);
    }

    /**
     * Get items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->getAdditionalProperty('items', []);
    }

    /**
     * Return true if invoice is paid.
     *
     * @return bool
     */
    public function isPaid()
    {
        return !($this->getBalance() > 0);
    }

    /**
     * Return true if invoice is canceled.
     *
     * @return bool
     */
    public function isCanceled()
    {
        return !($this->getAmount() > 0);
    }
}
