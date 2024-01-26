<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseInvoices class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class BaseInvoices extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'invoices' : 'Invoices';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'invoices';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'based_on_type', 'based_on_id', 'number', 'purchase_order_number', 'company_id', 'company_name', 'company_address', 'currency_id', 'language_id', 'project_id', 'discount_rate', 'subtotal', 'discount', 'tax', 'total', 'balance_due', 'paid_amount', 'last_payment_on', 'note', 'private_note', 'second_tax_is_enabled', 'second_tax_is_compound', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'due_on', 'issued_on', 'sent_on', 'recipients', 'email_from_id', 'email_from_name', 'email_from_email', 'email_subject', 'email_body', 'reminder_sent_on', 'closed_on', 'closed_by_id', 'closed_by_name', 'closed_by_email', 'is_canceled', 'is_muted', 'hash', 'is_trashed', 'trashed_on', 'trashed_by_id'];

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
        return 'Invoice';
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