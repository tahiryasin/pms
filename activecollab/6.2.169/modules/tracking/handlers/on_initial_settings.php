<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_initial_settings event handler.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage handlers
 */
function tracking_handle_on_initial_settings(array &$settings)
{
    $settings['default_job_type_id'] = JobTypes::getDefaultId();
    $settings['default_expense_category_id'] = ExpenseCategories::getDefaultId();
    $settings['default_is_tracking_enabled'] = ConfigOptions::getValue('default_is_tracking_enabled');
    $settings['default_is_client_reporting_enabled'] = ConfigOptions::getValue('default_is_client_reporting_enabled');
    $settings['default_project_budget_type'] = ConfigOptions::getValue('default_project_budget_type');
    $settings['default_tracking_objects_are_billable'] = ConfigOptions::getValue('default_tracking_objects_are_billable');
    $settings['default_members_can_change_billable'] = ConfigOptions::getValue('default_members_can_change_billable');
    $settings['rounding_interval'] = ConfigOptions::getValue('rounding_interval');
    $settings['minimal_time_entry'] = ConfigOptions::getValue('minimal_time_entry');
    $settings['rounding_enabled'] = ConfigOptions::getValue('rounding_enabled');
}
