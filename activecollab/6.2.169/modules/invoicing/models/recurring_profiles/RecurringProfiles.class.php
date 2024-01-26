<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Recurring profiles manager class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
class RecurringProfiles extends BaseRecurringProfiles
{
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

        if ($collection_name === 'active_profiles') {
            // do nothing
        } elseif (str_starts_with($collection_name, 'expired_profiles')) {
            $collection->setOrderBy('start_on DESC, id DESC');

            $bits = explode('_', $collection_name);
            $collection->setPagination(array_pop($bits), 30);
        } else {
            throw new InvalidParamError('collection_name', $collection_name);
        }

        return $collection;
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        self::prepareAttributesForNewProfile($attributes);

        try {
            DB::beginWork('Begin: create new recurring profile @ ' . __CLASS__);

            $recurring_profile = parent::create($attributes, $save, $announce); // @TODO Announcement should send after items are added to the invoice

            if ($recurring_profile instanceof RecurringProfile) {
                $recurring_profile->addItemsFromAttributes($attributes);
            }

            DB::commit('Done: create new recurring profile @ ' . __CLASS__);

            if ($recurring_profile instanceof RecurringProfile) {
                self::processProfile($recurring_profile, DateTimeValue::now()->getSystemDate());
            }

            return $recurring_profile;
        } catch (Exception $e) {
            DB::rollback('Rollback: create new recurring profile @ ' . __CLASS__);
            throw $e;
        }
    }

    /**
     * Prepare attributes for new recurring profile (we pull a lot of info from the client company).
     *
     * @param  array             $attributes
     * @throws InvalidParamError
     */
    private static function prepareAttributesForNewProfile(array &$attributes)
    {
        $company = isset($attributes['company_id']) && $attributes['company_id']
            ? DataObjectPool::get(Company::class, $attributes['company_id'])
            : null;

        if ($company instanceof Company) {
            if ($company->getIsOwner()) {
                throw new InvalidParamError(
                    'attributes[company_id]',
                    $attributes['company_id'],
                    "Can't issue internal invoice"
                );
            }

            if (empty($attributes['company_name'])) {
                $attributes['company_name'] = $company->getName();
            }

            if (empty($attributes['company_address'])) {
                $attributes['company_address'] = $company->getAddress();
            }

            if (empty($attributes['currency_id'])) {
                $attributes['currency_id'] = $company->getCurrencyId()
                    ? $company->getCurrencyId()
                    : Currencies::getDefaultId();
            }
        }
    }

    /**
     * Update an invoice.
     *
     * @param  RecurringProfile|DataObject $instance
     * @param  array                       $attributes
     * @param  bool                        $save
     * @return RecurringProfile
     * @throws Exception
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        try {
            DB::beginWork('Begin: update the recurring profile @ ' . __CLASS__);

            if (isset($attributes['start_on'])) {
                $instance->validateStartOn(DateValue::makeFromString($attributes['start_on']));
            }

            parent::update($instance, $attributes, $save);
            $instance->updateItemsFromAttributes($attributes);

            DB::commit('Done: update the recurring profile @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: update the recurring profile @ ' . __CLASS__);
            throw $e;
        }

        self::processProfile($instance, DateTimeValue::now()->getSystemDate());

        return $instance;
    }

    /**
     * Process profile.
     *
     * @param  RecurringProfile $recurring_profile
     * @param  DateValue        $date
     * @return Invoice|null
     */
    private static function processProfile(RecurringProfile $recurring_profile, DateValue $date)
    {
        if ($recurring_profile->shouldSendOn($date)) {
            $invoice = $recurring_profile->createInvoice(
                AngieApplication::nextInvoiceNumberSuggester()->suggest(),
                null,
                null,
                [
                    'trigger_date' => $date,
                ]
            );

            $safe_to_send = true;

            if (AngieApplication::isOnDemand()) {
                $safe_to_send = OnDemand::isItSafeToSendInvoice(
                    $recurring_profile,
                    $recurring_profile->getRecipientInstances()
                );
            }

            if ($recurring_profile->getAutoIssue() && $safe_to_send) {
                $invoice->send(
                    $recurring_profile->getCreatedBy(),
                    Users::findByAddressList($recurring_profile->getRecipients()),
                    $recurring_profile->getEmailSubject(),
                    $recurring_profile->getEmailBody()
                );

                AngieApplication::notifications()
                    ->notifyAbout('invoicing/invoice_generated_via_recurring_profile', $invoice)
                    ->setProfile($recurring_profile)
                    ->setInvoice($invoice)
                    ->sendToFinancialManagers(true);
            } else {
                AngieApplication::notifications()
                    ->notifyAbout('invoicing/draft_invoice_created_via_recurring_profile', $invoice)
                    ->setProfile($recurring_profile)
                    ->sendToFinancialManagers(true);
            }

            return $invoice;
        }

        return null;
    }

    /**
     * Returns true if $user can create new recurring profiles.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isFinancialManager();
    }
}
