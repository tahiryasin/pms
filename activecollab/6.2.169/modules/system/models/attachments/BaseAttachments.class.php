<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseAttachments class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseAttachments extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'attachments' : 'Attachments';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'attachments';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'type', 'parent_type', 'parent_id', 'name', 'mime_type', 'size', 'location', 'md5', 'disposition', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'raw_additional_properties', 'search_content', 'project_id', 'is_hidden_from_clients'];

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
        return 'Attachment';
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
        return 'created_on, id';
    }
}
