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

/**
 * @param array $settings
 */
function tracking_handle_on_initial_settings(array &$settings)
{
    $settings['default_job_type_id'] = JobTypes::getDefaultId();
    $settings['default_expense_category_id'] = ExpenseCategories::getDefaultId();
    $settings['default_is_tracking_enabled'] = ConfigOptions::getValue('default_is_tracking_enabled');
    $settings['default_is_client_reporting_enabled'] = ConfigOptions::getValue('default_is_client_reporting_enabled');
}
