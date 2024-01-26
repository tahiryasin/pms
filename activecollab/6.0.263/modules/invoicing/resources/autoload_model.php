<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.invoicing
 * @subpackage resources
 */
AngieApplication::useModel(
    [
        'estimates',
        'invoice_item_templates',
        'invoice_items',
        'invoice_note_templates',
        'invoices',
        'recurring_profiles',
        'remote_invoices',
        'tax_rates',
    ],
    'invoicing'
);
