<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseUsers class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseUsers extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'users' : 'Users';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'users';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'type', 'company_id', 'language_id', 'first_name', 'last_name', 'title', 'email', 'phone', 'im_type', 'im_handle', 'password', 'password_hashed_with', 'password_reset_key', 'password_reset_on', 'avatar_location', 'daily_capacity', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'is_archived', 'original_is_archived', 'archived_on', 'is_trashed', 'original_is_trashed', 'trashed_on', 'trashed_by_id', 'raw_additional_properties', 'is_eligible_for_covid_discount', 'first_login_on', 'paid_on', 'policy_version', 'policy_accepted_on'];

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
        return 'User';
    }

    /**
     * Return whether instance class name should be loaded from a field, or based on table name.
     *
     * @return string
     */
    public static function getInstanceClassNameFrom()
    {
        return DataManager::CLASS_NAME_FROM_FIELD;
    }

    /**
     * Return name of the field from which we will read instance class.
     *
     * @return string
     */
    public static function getInstanceClassNameFromField()
    {
        return 'type';
    }

    /**
     * Return name of this model.
     *
     * @return string
     */
    public static function getDefaultOrderBy()
    {
        return 'order_by';
    }
}
