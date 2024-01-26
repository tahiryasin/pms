<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\App\Mode\ApplicationModeInterface as ApplicationModeInterfaceAlias;
use ActiveCollab\Module\Invoicing\Utils\VariableProcessor\Factory\VariableProcessorFactoryInterface;

class Invoices extends BaseInvoices
{
    const DEFAULT_TASK_DESCRIPTION_FORMAT = 'Task #:task_number: :task_summary (:project_name)';
    const DEFAULT_PROJECT_DESCRIPTION_FORMAT = 'Project :name';
    const DEFAULT_JOB_TYPE_DESCRIPTION_FORMAT = ':job_type';
    const DEFAULT_INDIVIDUAL_DESCRIPTION_FORMAT = ':parent_task_or_project:record_summary (:record_date)';
    const SUMMARY_PUT_IN_PARENTHESES = 'put_in_parentheses';
    const SUMMARY_PREFIX_WITH_DASH = 'prefix_with_dash';
    const SUMMARY_SUFIX_WITH_DASH = 'sufix_with_dash';
    const SUMMARY_PREFIX_WITH_COLON = 'prefix_with_colon';
    const SUMMARY_SUFIX_WITH_COLON = 'sufix_with_colon';

    /**
     * Return new collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if ($collection_name == 'active_invoices') {
            $collection->setConditions('closed_on IS NULL AND is_trashed = ?', false);
            $collection->setOrderBy('created_on DESC');
        } elseif (str_starts_with($collection_name, 'archived_invoices')) {
            $collection->setConditions('closed_on IS NOT NULL AND is_trashed = ?', false);
            $collection->setOrderBy('issued_on DESC');

            $bits = explode('_', $collection_name);
            $collection->setPagination(array_pop($bits), 30);
        } elseif (str_starts_with($collection_name, 'company_invoices')) {
            $bits = explode('_', $collection_name);

            $page = array_pop($bits);
            array_pop($bits); // _page_

            $company = DataObjectPool::get('Company', array_pop($bits));

            if ($company instanceof Company && !$company->getIsOwner()) {
                $collection->setConditions('company_id = ? AND is_trashed = ?', $company->getId(), false);
                $collection->setPagination($page, 30);
            } else {
                throw new ImpossibleCollectionError('Company not found or owner company found');
            }
        } else {
            throw new InvalidParamError('collection_name', $collection_name);
        }

        return $collection;
    }

    public static function getPrivateNotes(): array
    {
        $result = [];

        $rows = DB::execute('SELECT `id`, `private_note` FROM `invoices`');

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $result[$row['id']] = (string) $row['private_note'];
            }
        }

        return $result;
    }

    // ---------------------------------------------------
    //  Utils
    // ---------------------------------------------------

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        self::prepareAttributesForNewInvoice($attributes);

        try {
            DB::beginWork('Begin: create new invoice @ ' . __CLASS__);

            $invoice = parent::create($attributes, false, false);

            if ($invoice instanceof Invoice && $save) {
                $invoice->dontUpdateSearchIndexOnNextSave();
                $invoice->save();

                $invoice->addItemsFromAttributes($attributes);

                if (self::shouldProcessVariables() && empty($attributes['skip_variable_processing'])) {
                    $invoice->processVariables(
                        AngieApplication::getContainer()
                            ->get(VariableProcessorFactoryInterface::class)
                                ->createFromInvoice($invoice)
                    );
                }

                AngieApplication::search()->add($invoice);
            }

            DB::commit('Done: create new invoice @ ' . __CLASS__);

            if ($save) {
                DataObjectPool::introduce($invoice);
            }

            return DataObjectPool::announce($invoice, DataObjectPool::OBJECT_CREATED, $attributes);
        } catch (Exception $e) {
            DB::rollback('Rollback: create new invoice @ ' . __CLASS__);
            throw $e;
        }
    }

    /**
     * Prepare attributes for new invoice (we pull a lot of info from the client company).
     *
     * @param  array             $attributes
     * @throws InvalidParamError
     */
    private static function prepareAttributesForNewInvoice(array &$attributes)
    {
        $company = !empty($attributes['company_id'])
            ? DataObjectPool::get(Company::class, $attributes['company_id'])
            : null;

        if ($company instanceof Company) {
            if ($company->getIsOwner()) {
                throw new InvalidParamError('attributes[company_id]', $attributes['company_id'], "Can't issue internal invoices");
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
        } else {
            $attributes['company_id'] = 0;
        }

        $attributes['second_tax_is_enabled'] = self::isSecondTaxEnabled();
        $attributes['second_tax_is_compound'] = self::isSecondTaxCompound();
        $attributes['discount_rate'] = empty($attributes['discount_rate'])
            ? 0
            : floor($attributes['discount_rate'] * 100) / 100; // be sure that discount has max two digits
    }

    /**
     * Check if second tax is enabled.
     *
     * @return bool
     */
    public static function isSecondTaxEnabled()
    {
        return (bool) ConfigOptions::getValue('invoice_second_tax_is_enabled');
    }

    /**
     * Check if second tax is compound.
     *
     * @return bool
     */
    public static function isSecondTaxCompound()
    {
        return self::isSecondTaxEnabled() && ConfigOptions::getValue('invoice_second_tax_is_compound');
    }

    /**
     * Update an invoice.
     *
     * @param  Invoice|DataObject $instance
     * @param  array              $attributes
     * @param  bool               $save
     * @return Invoice
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        if (array_key_exists('second_tax_is_enabled', $attributes)) {
            unset($attributes['second_tax_is_enabled']);
        }
        if (array_key_exists('second_tax_is_compound', $attributes)) {
            unset($attributes['second_tax_is_compound']);
        }

        // Ensure that discount has max two digits
        $attributes['discount_rate'] = empty($attributes['discount_rate'])
            ? 0
            : floor($attributes['discount_rate'] * 100) / 100;

        try {
            DB::beginWork('Begin: update the invoice @ ' . __CLASS__);

            $instance->dontUpdateSearchIndexOnNextSave();

            parent::update($instance, $attributes, $save);
            $instance->updateItemsFromAttributes($attributes);

            if (self::shouldProcessVariables()) {
                $instance->processVariables(
                    AngieApplication::getContainer()
                        ->get(VariableProcessorFactoryInterface::class)
                            ->createFromInvoice($instance)
                );
            }

            AngieApplication::search()->update($instance);

            DB::commit('Done: update the invoice @ ' . __CLASS__);

            return $instance;
        } catch (Exception $e) {
            DB::rollback('Rollback: update the invoice @ ' . __CLASS__);
            throw $e;
        }
    }

    public static function shouldProcessVariables(): bool
    {
        return AngieApplication::getContainer()->get(ApplicationModeInterfaceAlias::class)->isInTestMode()
            || AngieApplication::featureFlags()->isEnabled('variables_in_invoices');
    }

    /**
     * Returns true if $user can create new invoices.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Returns true if $user can manage $company finances.
     *
     * @param  Company|null $company
     * @param  User|null    $user
     * @return bool
     */
    public static function canManageClientCompanyFinances($company, $user)
    {
        if ($company instanceof Company) {
            return $user instanceof Client
                && $user->canManageCompanyFinances()
                && $user->getCompanyId() == $company->getId();
        } else {
            return false;
        }
    }

    /**
     * Return list of financial managers.
     *
     * @param  User     $exclude_user
     * @return Member[]
     */
    public static function findFinancialManagers($exclude_user = null)
    {
        $managers = [];

        $all_admins_and_managers = Users::findByType([Owner::class, Member::class]);

        if ($all_admins_and_managers) {
            $exclude_user_id = $exclude_user instanceof User ? $exclude_user->getId() : null;

            foreach ($all_admins_and_managers as $user) {
                if ($exclude_user_id && $user->getId() == $exclude_user_id) {
                    continue;
                }

                if ($user->isOwner() || $user->getSystemPermission(User::CAN_MANAGE_FINANCES)) {
                    $managers[] = $user;
                }
            }
        }

        return $managers;
    }

    /**
     * Return available company names and addresses that can be used for new invoices, estimates, and recurring profiles.
     *
     * @return array
     */
    public static function getCompanyAddresses(): array
    {
        $result = [];
        $saved_company_ids = [];

        $add_to_result = function ($id, $name, $currency_id, $address, ?DateTimeValue $timestamp, array &$result) {
            $timestamp = !is_null($timestamp) ? $timestamp : new DateTimeValue();
            $key = trim(strtolower_utf($name));

            if (empty($result[$key]) || $result[$key]['timestamp'] < $timestamp->getTimestamp()) {
                $result[$key] = [
                    'id' => $id,
                    'name' => $name,
                    'currency_id' => $currency_id,
                    'address' => $address,
                    'timestamp' => $timestamp->getTimestamp(),
                ];
            }
        };

        $saved_companies = DB::execute(
            "SELECT `id`, `name`, `currency_id`, `address`, `updated_on` AS 'timestamp' FROM `companies` WHERE `is_owner` = ?",
            false
        );

        if (!empty($saved_companies)) {
            $saved_companies->setCasting(
                [
                    'timestamp' => DBResult::CAST_DATETIME,
                ]
            );

            foreach ($saved_companies as $saved_company) {
                $add_to_result(
                    $saved_company['id'],
                    $saved_company['name'],
                    $saved_company['currency_id'],
                    $saved_company['address'],
                    $saved_company['timestamp'],
                    $result
                );
                $saved_company_ids[] = $saved_company['id'];
            }
        }

        foreach (['invoices', 'estimates', 'recurring_profiles'] as $table) {
            $latest_company_addresses = DB::execute(
                "SELECT company_id, company_name, currency_id, company_address, created_on AS 'timestamp' FROM $table WHERE created_on = (SELECT MAX(created_on) FROM $table AS t WHERE t.company_name = $table.company_name)"
            );

            if (!empty($latest_company_addresses)) {
                $latest_company_addresses->setCasting(['timestamp' => DBResult::CAST_DATETIME]);

                foreach ($latest_company_addresses as $latest_company_address) {
                    if (!in_array($latest_company_address['company_id'], $saved_company_ids)) {
                        $add_to_result(
                            $latest_company_address['company_id'],
                            $latest_company_address['company_name'],
                            $latest_company_address['currency_id'],
                            $latest_company_address['company_address'],
                            $latest_company_address['timestamp'],
                            $result
                        );
                    }
                }
            }
        }

        usort(
            $result,
            function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );

        return $result;
    }

    // Record summary transformation

    /**
     * Return PDF file name.
     *
     * @param  Invoice $invoice
     * @return string
     */
    public static function getInvoicePdfName(Invoice $invoice)
    {
        return self::getInvoiceName($invoice->getNumber()) . '.pdf';
    }

    /**
     * Return invoice name based on the set of given parameters.
     *
     * This method was extracted so we can use it in reports, and other application areas without
     * creating a new invoice instance in order to get its properly formatted name
     *
     * @param  string $number
     * @param  bool   $short
     * @return string
     */
    public static function getInvoiceName($number, $short = false)
    {
        return $short ? $number : lang('Invoice #:invoice_num', ['invoice_num' => $number]);
    }

    /**
     * Generate task line description.
     *
     * @param  array  $variables
     * @return string
     */
    public static function generateTaskDescription($variables)
    {
        return self::generateDescription(
            'description_format_grouped_by_task',
            self::DEFAULT_TASK_DESCRIPTION_FORMAT,
            $variables
        );
    }

    /**
     * Generate description based on pattern and variables.
     *
     * @param  string $pattern_config_option
     * @param  string $default_pattern
     * @param  array  $variables
     * @return mixed
     */
    private static function generateDescription($pattern_config_option, $default_pattern, $variables)
    {
        $pattern = ConfigOptions::getValue($pattern_config_option);
        if (empty($pattern)) {
            $pattern = $default_pattern;
        }

        $replacements = [];

        foreach ($variables as $k => $v) {
            $replacements[":$k"] = $v;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $pattern);
    }

    /**
     * Generate project line description.
     *
     * @param  array  $variables
     * @return string
     */
    public static function generateProjectDescription($variables)
    {
        return self::generateDescription(
            'description_format_grouped_by_project',
            self::DEFAULT_PROJECT_DESCRIPTION_FORMAT,
            $variables
        );
    }

    /**
     * Generate description when tracked data is grouped by job type.
     *
     * @param  array  $variables
     * @return string
     */
    public static function generateJobTypeDescription($variables)
    {
        return self::generateDescription(
            'description_format_grouped_by_job_type',
            self::DEFAULT_JOB_TYPE_DESCRIPTION_FORMAT,
            $variables
        );
    }

    /**
     * Generate individual item description.
     *
     * @param  array  $variables
     * @return string
     */
    public static function generateIndividualDescription($variables)
    {
        $summary = trim(array_var($variables, 'record_summary'));

        if ($summary) {
            $transformations = [
                ConfigOptions::getValue('first_record_summary_transformation'),
                ConfigOptions::getValue('second_record_summary_transformation'),
            ];

            foreach ($transformations as $transformation) {
                if ($transformation) {
                    switch ($transformation) {
                        case self::SUMMARY_PUT_IN_PARENTHESES:
                            $summary = "($summary)";
                            break;
                        case self::SUMMARY_PREFIX_WITH_DASH:
                            $summary = " - $summary";
                            break;
                        case self::SUMMARY_SUFIX_WITH_DASH:
                            $summary = "$summary - ";
                            break;
                        case self::SUMMARY_PREFIX_WITH_COLON:
                            $summary = ": $summary";
                            break;
                        case self::SUMMARY_SUFIX_WITH_COLON:
                            $summary = "$summary: ";
                            break;
                    }
                }
            }
        }

        $variables['record_summary'] = $summary;

        return self::generateDescription(
            'description_format_separate_items',
            self::DEFAULT_INDIVIDUAL_DESCRIPTION_FORMAT,
            $variables
        );
    }

    // ---------------------------------------------------
    //  Finders
    // ---------------------------------------------------

    /**
     * Find invoice by hash.
     *
     * @param  string             $hash
     * @return Invoice|DataObject
     */
    public static function findByHash($hash)
    {
        return self::find(
            [
                'conditions' => ['hash = ?', $hash],
                'one' => true,
            ]
        );
    }

    /**
     * Return ID-s by company.
     *
     * @param  Company $company
     * @return int[]
     */
    public static function findIdsByCompany(Company $company)
    {
        return DB::executeFirstRow('SELECT `id` FROM `invoices` WHERE `company_id` = ?', $company->getId());
    }

    /**
     * Return number of invoices that use $currency.
     *
     * @param  Currency $currency
     * @return int
     */
    public static function countByCurrency($currency): int
    {
        return self::count(
            [
                '`currency_id` = ?', $currency->getId(),
            ]
        );
    }

    /**
     * Method use to set update_on field to now on all invoices.
     */
    public static function bulkUpdateOn()
    {
        DB::execute('UPDATE `invoices` SET `updated_on` = UTC_TIMESTAMP()');
        self::clearCache();
    }
}
