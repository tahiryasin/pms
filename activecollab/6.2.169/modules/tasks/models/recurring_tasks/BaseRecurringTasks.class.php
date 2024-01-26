<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseRecurringTasks class.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
abstract class BaseRecurringTasks extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'recurring_tasks' : 'RecurringTasks';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'recurring_tasks';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'project_id', 'task_list_id', 'assignee_id', 'delegated_by_id', 'name', 'body', 'is_important', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'updated_by_id', 'updated_by_name', 'updated_by_email', 'start_in', 'due_in', 'job_type_id', 'estimate', 'position', 'is_hidden_from_clients', 'is_trashed', 'original_is_trashed', 'trashed_on', 'trashed_by_id', 'repeat_frequency', 'repeat_amount', 'repeat_amount_extended', 'triggered_number', 'last_trigger_on', 'fake_assignee_name', 'fake_assignee_email', 'raw_additional_properties'];

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
        return 'RecurringTask';
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
