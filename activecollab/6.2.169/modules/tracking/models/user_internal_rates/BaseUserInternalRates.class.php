<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseUserInternalRates class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
abstract class BaseUserInternalRates extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'user_internal_rates' : 'UserInternalRates';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'user_internal_rates';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'user_id', 'user_name', 'user_email', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'valid_from', 'rate', 'updated_on', 'updated_by_id', 'updated_by_name', 'updated_by_email'];

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
        return 'UserInternalRate';
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
        return 'id';
    }
}
