<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Estimates manager class.
 *
 * @package activeCollab.modules.invoicing
 * @subpackage models
 */
class Estimates extends BaseEstimates
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

        if ($collection_name == 'active_estimates') {
            $collection->setConditions('status NOT IN (?) AND is_trashed = ?', [Estimate::WON, Estimate::LOST], false);
        } else {
            if (str_starts_with($collection_name, 'archived_estimates')) {
                $collection->setConditions('status IN (?) AND is_trashed = ?', [Estimate::WON, Estimate::LOST], false);
                $collection->setOrderBy('updated_on DESC');

                $bits = explode('_', $collection_name);
                $collection->setPagination(array_pop($bits), 30);
            } else {
                throw new InvalidParamError('collection_name', $collection_name);
            }
        }

        return $collection;
    }

    /**
     * Return private notes for estimates.
     *
     * @return array
     */
    public static function getPrivateNotes()
    {
        $result = [];

        if ($rows = DB::execute('SELECT id, private_note FROM estimates')) {
            foreach ($rows as $row) {
                $result[$row['id']] = (string) $row['private_note'];
            }
        }

        return $result;
    }

    /**
     * Return estimate PDF file name.
     *
     * @param  Estimate $estimate
     * @return string
     */
    public static function getEstimatePdfName(Estimate $estimate)
    {
        return 'estimate-' . $estimate->getName() . '.pdf';
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        self::prepareAttributesForNewEstimate($attributes);

        try {
            DB::beginWork('Begin: create new estimate @ ' . __CLASS__);

            $estimate = parent::create($attributes, false, false);

            if ($estimate instanceof Estimate && $save) {
                $estimate->dontUpdateSearchIndexOnNextSave();
                $estimate->save();

                $estimate->addItemsFromAttributes($attributes);

                AngieApplication::search()->add($estimate);
            }

            DB::commit('Done: create new estimate @ ' . __CLASS__);

            return $estimate;
        } catch (Exception $e) {
            DB::rollback('Rollback: create new estimate @ ' . __CLASS__);
            throw $e;
        }
    }

    /**
     * Prepare attributes for new estimate (we pull a lot of info from the client company).
     *
     * @param  array             $attributes
     * @throws InvalidParamError
     */
    private static function prepareAttributesForNewEstimate(array &$attributes)
    {
        $company = isset($attributes['company_id']) && $attributes['company_id'] ? DataObjectPool::get('Company', $attributes['company_id']) : null;

        if ($company instanceof Company) {
            if ($company->getIsOwner()) {
                throw new InvalidParamError('attributes[company_id]', $attributes['company_id'], "Can't issue internal estimate");
            }

            if (empty($attributes['company_name'])) {
                $attributes['company_name'] = $company->getName();
            }

            if (empty($attributes['company_address'])) {
                $attributes['company_address'] = $company->getAddress();
            }

            if (empty($attributes['currency_id'])) {
                $attributes['currency_id'] = $company->getCurrencyId() ? $company->getCurrencyId() : Currencies::getDefaultId();
            }
        }
    }

    /**
     * Update an estimate.
     *
     * @param  Estimate|DataObject $instance
     * @param  array               $attributes
     * @param  bool                $save
     * @return Estimate
     * @throws Exception
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        $notify_on_total_update = $instance->isSent();

        if (array_key_exists('notify_on_total_update', $attributes)) {
            $notify_on_total_update = $instance->isSent() && $attributes['notify_on_total_update'];
            unset($attributes['notify_on_total_update']);
        }

        $current_total = $instance->getTotal();

        try {
            DB::beginWork('Begin: update the estimate @ ' . __CLASS__);

            $instance->dontUpdateSearchIndexOnNextSave();

            parent::update($instance, $attributes, $save);
            $instance->updateItemsFromAttributes($attributes);

            AngieApplication::search()->update($instance);

            DB::commit('Done: update the estimate @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: update the estimate @ ' . __CLASS__);
            throw $e;
        }

        if ($notify_on_total_update && $current_total != $instance->getTotal()) {
            AngieApplication::notifications()->notifyAbout('invoicing/estimate_updated', $instance, AngieApplication::authentication()->getLoggedUser())
                ->setOldTotal($current_total)
                ->sendToUsers($instance->getRecipientInstances(), true);
        }

        return $instance;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can create new estimate.
     *
     * Optonally, $based_on can be provided, so we can check if user can create
     * a new estimate based on a given project request
     *
     * @param  User                 $user
     * @throws InvalidInstanceError
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Method use to set update_on field to now on all estimates.
     */
    public static function bulkUpdateOn()
    {
        DB::execute('UPDATE estimates SET updated_on = UTC_TIMESTAMP()');
        self::clearCache();
    }
}
