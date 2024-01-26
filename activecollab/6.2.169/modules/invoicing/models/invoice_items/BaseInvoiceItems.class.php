<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseInvoiceItems class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class BaseInvoiceItems extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'invoice_items' : 'InvoiceItems';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'invoice_items';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'parent_type', 'parent_id', 'first_tax_rate_id', 'second_tax_rate_id', 'discount_rate', 'description', 'quantity', 'unit_cost', 'subtotal', 'discount', 'first_tax', 'second_tax', 'total', 'second_tax_is_enabled', 'second_tax_is_compound', 'position', 'project_id'];

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
        return 'InvoiceItem';
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
        return 'position';
    }
}
