<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_resets_initial_settings_timestamp event handler.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage handlers
 */

/**
 * @param array $config_options
 */
function tracking_handle_on_resets_initial_settings_timestamp(array &$config_options)
{
    $config_options[] = 'invoice_second_tax_is_compound';
    $config_options[] = 'invoice_second_tax_is_enabled';
    $config_options[] = 'default_is_tracking_enabled';
    $config_options[] = 'default_is_client_reporting_enabled';
}
