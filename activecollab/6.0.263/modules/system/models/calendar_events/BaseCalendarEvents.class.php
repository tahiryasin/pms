<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseCalendarEvents class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseCalendarEvents extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'calendar_events' : 'CalendarEvents';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'calendar_events';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'calendar_id', 'name', 'starts_on', 'starts_on_time', 'ends_on', 'ends_on_time', 'repeat_event', 'repeat_until', 'raw_additional_properties', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'is_trashed', 'original_is_trashed', 'trashed_on', 'trashed_by_id', 'note', 'position'];

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
        return 'CalendarEvent';
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
        return 'starts_on, starts_on_time, position';
    }
}
