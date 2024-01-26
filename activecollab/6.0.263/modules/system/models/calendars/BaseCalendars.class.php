<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseCalendars class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseCalendars extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'calendars' : 'Calendars';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'calendars';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'type', 'name', 'color', 'raw_additional_properties', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'is_trashed', 'trashed_on', 'trashed_by_id', 'position'];

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
        return 'Calendar';
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
        return 'position';
    }
}
