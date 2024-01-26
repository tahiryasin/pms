<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseDiscussions class.
 *
 * @package ActiveCollab.modules.discussions
 * @subpackage models
 */
abstract class BaseDiscussions extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'discussions' : 'Discussions';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'discussions';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'project_id', 'name', 'body', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'updated_by_id', 'updated_by_name', 'updated_by_email', 'is_hidden_from_clients', 'is_trashed', 'original_is_trashed', 'trashed_on', 'trashed_by_id'];

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
        return 'Discussion';
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
