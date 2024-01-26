<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.tracking
 * @subpackage resources
 */
AngieApplication::useModel(
    [
        'expense_categories',
        'expenses',
        'job_types',
        'stopwatches',
        'time_records',
        'user_internal_rates',
        'budget_thresholds',
        'budget_thresholds_notifications',
    ],
    'tracking'
);
