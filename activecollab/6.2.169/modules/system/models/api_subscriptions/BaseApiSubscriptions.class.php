<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseApiSubscriptions class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseApiSubscriptions extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'api_subscriptions' : 'ApiSubscriptions';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'api_subscriptions';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'user_id', 'token_id', 'client_name', 'client_vendor', 'created_on', 'last_used_on', 'requests_count'];

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
        return 'ApiSubscription';
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
