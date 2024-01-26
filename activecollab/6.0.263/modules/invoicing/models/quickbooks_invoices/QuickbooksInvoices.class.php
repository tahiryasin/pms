<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Quickbooks invoices class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
class QuickbooksInvoices extends RemoteInvoices
{
    const SYNC_TIMESTAMP_KEY = 'QB_SYNC_TIMESTAMP';

    /**
     * @var int
     */
    private static $sync_timestamp = 0;

    /**
     * Get sync timestamp.
     *
     * @return int
     */
    public static function getSyncTimestamp()
    {
        if (self::$sync_timestamp == 0) {
            self::$sync_timestamp = AngieApplication::memories()->get(self::SYNC_TIMESTAMP_KEY, 0);
        }

        return self::$sync_timestamp;
    }

    /**
     * Set sync timestamp.
     *
     * @param int $value
     */
    public static function setSyncTimestamp($value)
    {
        if ($value > self::getSyncTimestamp()) {
            self::$sync_timestamp = $value;
            AngieApplication::memories()->set(self::SYNC_TIMESTAMP_KEY, $value);
        }
    }

    /**
     * Return true if user can create new quickbooks invoice.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Return new collection.
     *
     * @param  string          $collection_name
     * @param  User|null       $user
     * @return ModelCollection
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);
        $collection->setConditions("type = 'QuickbooksInvoice'");

        return $collection;
    }
    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $quickbooks_invoice = null;

        DB::transact(
            function () use (&$quickbooks_invoice, $attributes, $save, $announce) {
                if (isset($attributes['items']) && is_array($attributes['items'])) {
                    foreach ($attributes['items'] as $key => &$item) {
                        $item['line_num'] = $key + 1;
                    }
                } else {
                    $attributes['items'] = [];
                }

                $qb_integration = self::getQuickbooksIntegration();

                if (isset($attributes['add_new_client']) && $attributes['add_new_client']) {
                    $new_client_name = $attributes['client_id'];
                    $client_id_with_existing_name = null;

                    if ($quickbooks_customers = $qb_integration->fetch('Customer', [], false)) {
                        foreach ($quickbooks_customers as $quickbooks_customer) {
                            $data = $quickbooks_customer->getRawData();
                            if (isset($data['DisplayName']) && strtolower($data['DisplayName']) === strtolower($new_client_name)) {
                                $client_id_with_existing_name = $quickbooks_customer->getId();
                                break;
                            }
                        }
                    }

                    if ($client_id_with_existing_name) {
                        $attributes['client_id'] = $client_id_with_existing_name;
                    } else {
                        $new_quickbooks_customer = $qb_integration->dataService()->setEntity('Customer')->create([
                            'DisplayName' => $new_client_name,
                        ]);
                        $attributes['client_id'] = $new_quickbooks_customer->getId();
                    }
                }

                $invoice = $qb_integration->createInvoice($attributes);
                $data = $invoice->getRawData();

                $attributes = array_merge(
                    $attributes,
                    [
                        'remote_code' => $invoice->getId(),
                        'amount' => $data['TotalAmt'],
                        'client' => $data['CustomerRef']['name'],
                        'invoice_number' => $data['DocNumber'],
                        'balance' => $data['Balance'],
                        'currency' => $data['CurrencyRef']['value'],
                    ]
                );

                $attributes['type'] = 'QuickbooksInvoice';

                if (isset($data['Line']) && is_array($data['Line'])) {
                    foreach ($data['Line'] as $line) {
                        $key = $line['LineNum'] - 1;
                        if (isset($attributes['items'][$key])) {
                            $attributes['items'][$key]['line_id'] = $line['Id'];
                        }
                    }
                }

                $quickbooks_invoice = parent::create($attributes, $save, $announce);
            },
            'Create quickbooks invoice'
        );

        return $quickbooks_invoice;
    }

    /**
     * Return data service.
     *
     * @return QuickbooksIntegration
     */
    public static function getQuickbooksIntegration()
    {
        return Integrations::findFirstByType('QuickbooksIntegration');
    }

    /**
     * Sync local quickbooks invoices with remote invoices.
     *
     * @return array
     */
    public static function sync()
    {
        $last_sync_date = new \DateTime('@' . self::getSyncTimestamp());
        $response = self::getQuickbooksIntegration()->dataService()->cdc(['Invoice'], $last_sync_date);

        $result = [
            'updated' => [],
            'deleted' => [],
        ];

        if (isset($response['Invoice'])) {
            foreach ($response['Invoice'] as $entity) {
                $quickbooks_invoice = self::find([
                    'conditions' => ['type = ? AND remote_code = ?', 'QuickbooksInvoice', $entity->getId()],
                    'one' => true,
                ]);

                if ($quickbooks_invoice instanceof QuickbooksInvoice) {
                    $data = $entity->getRawData();

                    if (isset($data['status']) && $data['status'] == 'Deleted') {
                        $result['deleted'][] = $quickbooks_invoice->getId();
                        self::scrap($quickbooks_invoice);
                    } else {
                        $attributes = [
                            'amount' => $data['TotalAmt'],
                            'client' => $data['CustomerRef']['name'],
                            'invoice_number' => $data['DocNumber'],
                            'balance' => $data['Balance'],
                            'currency' => $data['CurrencyRef']['value'],
                        ];

                        if (isset($data['EmailStatus']) && $data['EmailStatus'] != QuickbooksInvoice::EMAIL_STATUS_NOT_SET) {
                            $attributes['email_status'] = $data['EmailStatus'];
                        }

                        // prepare invoice items from quickbooks data service
                        if (isset($data['Line']) && is_array($data['Line'])) {
                            $line_ids = array_map(function ($line) {
                                return isset($line['Id']) ? $line['Id'] : 0;
                            }, $data['Line']);

                            $attributes['items'] = array_filter($quickbooks_invoice->getItems(), function ($item) use ($line_ids) {
                                return isset($item['line_id']) && in_array($item['line_id'], $line_ids);
                            });
                        } else {
                            $attributes['items'] = [];
                        }

                        self::update($quickbooks_invoice, $attributes);

                        $result['updated'][] = $quickbooks_invoice;
                    }
                }
            }
        }

        self::setSyncTimestamp(DateTimeValue::now()->getTimestamp());

        return $result;
    }

    /**
     * Update quickbooks invoice.
     *
     * @param  DataObject|QuickBooksInvoice &$instance
     * @param  array                        $attributes
     * @param  bool                         $save
     * @return DataObject|void
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        if (isset($attributes['email_status'])) {
            $instance->setEmailStatus($attributes['email_status']);
        }

        if (isset($attributes['currency'])) {
            $instance->setCurrency($attributes['currency']);
        }

        if (isset($attributes['amount']) && !($attributes['amount'] > 0)) {
            $attributes['items'] = [];
        }

        parent::update($instance, $attributes, $save);

        return $instance;
    }
}
