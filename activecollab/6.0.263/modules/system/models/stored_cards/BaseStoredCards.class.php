<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseStoredCards class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseStoredCards extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'stored_cards' : 'StoredCards';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'stored_cards';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'payment_gateway_id', 'gateway_card_id', 'brand', 'last_four_digits', 'expiration_month', 'expiration_year', 'card_holder_id', 'card_holder_name', 'card_holder_email', 'address_line_1', 'address_line_2', 'address_zip', 'address_city', 'address_country'];

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
        return 'StoredCard';
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
