<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_resets_initial_settings_timestamp event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * @param array $config_options
 */
function system_handle_on_resets_initial_settings_timestamp(array &$config_options)
{
    $config_options[] = 'show_sample_projects_wizard_step';
    $config_options[] = 'workload_enabled';
}
