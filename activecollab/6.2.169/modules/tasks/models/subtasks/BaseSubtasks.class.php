<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseSubtasks class.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
abstract class BaseSubtasks extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'subtasks' : 'Subtasks';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'subtasks';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'task_id', 'assignee_id', 'delegated_by_id', 'body', 'due_on', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'completed_on', 'completed_by_id', 'completed_by_name', 'completed_by_email', 'position', 'is_trashed', 'original_is_trashed', 'trashed_on', 'trashed_by_id', 'fake_assignee_name', 'fake_assignee_email'];

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
        return 'Subtask';
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
        return 'ISNULL(position) ASC, position, created_on';
    }
}
