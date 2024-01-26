<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseProjects class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseProjects extends DataManager
{
    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @return string
     */
    public static function getModelName($underscore = false)
    {
        return $underscore ? 'projects' : 'Projects';
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'projects';
    }

    /**
     * All table fields.
     *
     * @var array
     */
    private static $fields = ['id', 'template_id', 'based_on_type', 'based_on_id', 'company_id', 'category_id', 'label_id', 'currency_id', 'budget_type', 'budget', 'name', 'leader_id', 'body', 'completed_on', 'completed_by_id', 'completed_by_name', 'completed_by_email', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'updated_by_id', 'updated_by_name', 'updated_by_email', 'last_activity_on', 'project_hash', 'is_tracking_enabled', 'is_billable', 'members_can_change_billable', 'is_client_reporting_enabled', 'is_trashed', 'trashed_on', 'trashed_by_id', 'is_sample'];

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
        return 'Project';
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
        return 'ISNULL(completed_on) DESC, name';
    }
}
