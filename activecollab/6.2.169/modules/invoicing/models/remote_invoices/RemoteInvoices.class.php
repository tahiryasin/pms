<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * RemoteInvoices class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class RemoteInvoices extends BaseRemoteInvoices
{
    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : [];
        $items_for_fixed = array_map(function ($item) {
            return [
                'amount' => isset($item['unit_cost']) ? $item['unit_cost'] : 0,
                'project_id' => isset($item['project_id']) ? $item['project_id'] : 0,
                'line_id' => isset($item['line_id']) ? $item['line_id'] : '',
            ];
        }, $items);

        $time_record_ids = self::getRecordIdsFromItemsByRecordType($items, 'time_record');
        $expense_ids = self::getRecordIdsFromItemsByRecordType($items, 'expense');

        try {
            DB::beginWork('Begin: update time records and expenses id to pending payment status @ ' . __CLASS__);

            if (count($time_record_ids)) {
                DB::execute('UPDATE time_records SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', TimeRecord::PENDING_PAYMENT, $time_record_ids);

                TimeRecords::clearCacheFor($time_record_ids);
            }

            if (count($expense_ids)) {
                DB::execute('UPDATE expenses SET  billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', Expense::PENDING_PAYMENT, $expense_ids);

                Expenses::clearCacheFor($expense_ids);
            }

            /** @var RemoteInvoice $invoice */
            $invoice = parent::create($attributes, false, false);
            $invoice->setItems($items);
            $invoice->save();

            if (isset($attributes['based_on']) && $attributes['based_on'] === 'fixed') {
                foreach ($items_for_fixed as $item) {
                    $item_attributes = array_merge($item,
                        [
                            'parent_id' => $invoice->getId(),
                            'parent_type' => get_class($invoice) ?? 'QuickbooksInvoice',
                        ]
                    );

                    RemoteInvoiceItems::create($item_attributes);
                }
            }

            DB::commit('Done: update time records and expenses id to pending payment status @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: update time records and expenses id to pending payment status @ ' . __CLASS__);
            throw $e;
        }

        return $invoice;
    }

    /**
     * Return record ids from items by record type.
     *
     * @param  string $type
     * @return array
     */
    public static function getRecordIdsFromItemsByRecordType(array $items, $type)
    {
        $result = [];
        $key = $type . '_ids';

        foreach ($items as $item) {
            if (isset($item[$key]) && is_array($item[$key])) {
                $result = array_merge($result, $item[$key]);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        /** @var RemoteInvoice $instance $items */
        $items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : $instance->getItems();

        $prev_time_record_ids = self::getRecordIdsFromItemsByRecordType($instance->getItems(), 'time_record');
        $time_record_ids = self::getRecordIdsFromItemsByRecordType($items, 'time_record');
        $restore_time_record_ids = array_diff($prev_time_record_ids, $time_record_ids);

        $prev_expense_ids = self::getRecordIdsFromItemsByRecordType($instance->getItems(), 'expense');
        $expense_ids = self::getRecordIdsFromItemsByRecordType($items, 'expense');
        $restore_expense_ids = array_diff($prev_expense_ids, $expense_ids);

        try {
            DB::beginWork('Begin: update time records and expenses id to pending payment status @ ' . __CLASS__);

            parent::update($instance, $attributes, false);

            $time_record_status = $instance->isPaid() ? TimeRecord::PAID : TimeRecord::PENDING_PAYMENT;
            $expense_status = $instance->isPaid() ? Expense::PAID : Expense::PENDING_PAYMENT;

            // TimeRecords
            if (count($restore_time_record_ids)) {
                DB::execute('UPDATE time_records SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', TimeRecord::BILLABLE, $restore_time_record_ids);
            }
            if (count($time_record_ids)) {
                DB::execute('UPDATE time_records SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', $time_record_status, $time_record_ids);
            }
            TimeRecords::clearCacheFor($prev_time_record_ids);

            // Expenses
            if (count($restore_expense_ids)) {
                DB::execute('UPDATE expenses SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', Expense::BILLABLE, $restore_expense_ids);
            }
            if (count($expense_ids)) {
                DB::execute('UPDATE expenses SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', $expense_status, $expense_ids);
            }
            Expenses::clearCacheFor($prev_expense_ids);

            $instance->setItems($items);
            $instance->save();

            if ($instance->getBasedOn() === 'fixed' && isset($attributes['items']) && is_array($attributes['items'])) {
                $new_item_ids = array_keys($attributes['items']);
                $prev_items = RemoteInvoiceItems::findBy(['parent_id' => $instance->getId()]);
                if ($prev_items) {
                    /** @var RemoteInvoiceItem[] $items */
                    $items = $prev_items->toArray();
                    foreach ($items as $prev_item) {
                        $item_id = $prev_item->getLineId();
                        if (in_array($item_id, $new_item_ids)) {
                            $prev_item->setAmount((float) $attributes['items'][$item_id]);
                            $prev_item->save();
                        }
                    }
                }
            }

            DB::commit('Done: update time records and expenses id to pending payment status @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: update time records and expenses id to pending payment status @ ' . __CLASS__);
            throw $e;
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public static function scrap(DataObject &$instance, $force_delete = false)
    {
        /* @var RemoteInvoice $instance */
        $time_record_ids = self::getRecordIdsFromItemsByRecordType($instance->getItems(), 'time_record');
        $expense_ids = self::getRecordIdsFromItemsByRecordType($instance->getItems(), 'expense');

        try {
            DB::beginWork('Begin: update time records and expenses id to billable payment status @ ' . __CLASS__);

            // TimeRecords
            if (count($time_record_ids)) {
                DB::execute('UPDATE time_records SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', TimeRecord::BILLABLE, $time_record_ids);
                TimeRecords::clearCacheFor($time_record_ids);
            }

            // Expenses
            if (count($expense_ids)) {
                DB::execute('UPDATE expenses SET billable_status = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', TimeRecord::BILLABLE, $expense_ids);
                Expenses::clearCacheFor($expense_ids);
            }

            if ($instance->getBasedOn() === 'fixed') {
                $invoice_items = RemoteInvoiceItems::findBy(['parent_id' => $instance->getId()]);
                if ($invoice_items) {
                    /** @var RemoteInvoiceItem[] $items */
                    $items = $invoice_items->toArray();
                    foreach ($items as $item) {
                        $item->delete();
                    }
                }
            }

            parent::scrap($instance, $force_delete);

            DB::commit('Done: update time records and expenses id to billable payment status @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: update time records and expenses id to billable payment status @ ' . __CLASS__);
            throw $e;
        }
    }

    /**
     * Return new collection.
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if ($collection_name == 'active_remote_invoices') {
            $collection->setOrderBy('updated_on DESC');
        } elseif (str_starts_with($collection_name, 'archived_remote_invoices')) {
            $collection->setConditions('amount = balance');
            $collection->setOrderBy('updated_on DESC');

            $bits = explode('_', $collection_name);
            $collection->setPagination(array_pop($bits), 30);
        } else {
            throw new InvalidParamError('collection_name', $collection_name);
        }

        return $collection;
    }

    /**
     * Update local invoices balance with new one.
     */
    public static function updateBalance(array $balances)
    {
        foreach ($balances as $balance) {
            DB::execute('UPDATE remote_invoices SET balance = ?, updated_on = UTC_TIMESTAMP() WHERE remote_code = ?', $balance['balance'], $balance['remote_code']);
        }
        AngieApplication::cache()->removeByModel(static::getModelName(true));
    }

    abstract public static function countInvoicesStatus();

    protected static function countByStatus(string $type)
    {
        /** @var RemoteInvoice[] $remote_invoices */
        $remote_invoices = self::find(['conditions' => ['type = ?', $type]]);

        $statuses = self::prepareInitialValues();

        if (is_foreachable($remote_invoices)) {
            foreach ($remote_invoices as $remote_invoice) {
                ++$statuses[$remote_invoice->getStatus()];
            }
        }

        return [
            $remote_invoices ? count($remote_invoices) : 0,
            $statuses[RemoteInvoice::PAID],
            $statuses[RemoteInvoice::PAID_AND_CANCELED],
            $statuses[RemoteInvoice::UNSENT],
            $statuses[RemoteInvoice::SENT],
            $statuses[RemoteInvoice::PARTIALLY_PAID],
        ];
    }

    private static function prepareInitialValues(): array
    {
        $statuses = [];

        foreach (RemoteInvoice::STATUSES as $status) {
            $statuses[$status] = 0;
        }

        return $statuses;
    }
}