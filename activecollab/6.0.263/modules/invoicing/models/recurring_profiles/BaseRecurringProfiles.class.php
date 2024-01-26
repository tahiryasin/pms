<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseRecurringProfiles class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class BaseRecurringProfiles extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'recurring_profiles' : 'RecurringProfiles';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'recurring_profiles';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'name', 'stored_card_id', 'purchase_order_number', 'company_id', 'company_name', 'company_address', 'currency_id', 'language_id', 'project_id', 'discount_rate', 'subtotal', 'discount', 'tax', 'total', 'balance_due', 'paid_amount', 'note', 'private_note', 'second_tax_is_enabled', 'second_tax_is_compound', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'start_on', 'invoice_due_after', 'frequency', 'occurrences', 'triggered_number', 'last_trigger_on', 'auto_issue', 'recipients', 'email_from_id', 'email_subject', 'email_body', 'is_enabled'];

    /**
     * Return a list of model fields.
     *
     * @return array
     */
    public static function getFields()
    {
        return self::$fields;
    }

    /**
     * Return class name of a single instance.
     *
     * @return string
     */
    public static function getInstanceClassName()
    {
        return 'RecurringProfile';
    }

    /**
     * Return whether instance class name should be loaded from a field, or based on table name.
     *
     * @return string
     */
    public static function getInstanceClassNameFrom()
    {
        return DataManager::CLASS_NAME_FROM_TABLE;
    }

    /**
     * Return name of the field from which we will read instance class.
     *
     * @return string
     */
    public static function getInstanceClassNameFromField()
    {
        return '';
    }

    /**
     * Return name of this model.
     *
     * @return string
     */
    public static function getDefaultOrderBy()
    {
        return '';
    }
}
