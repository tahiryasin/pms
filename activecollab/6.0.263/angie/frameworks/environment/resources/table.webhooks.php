<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Webhooks table definition.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('webhooks')
    ->addColumns(
        [
            new DBIdColumn(),
            DBTypeColumn::create(Webhook::class),
            DBFkColumn::create('integration_id'),
            DBNameColumn::create(100),
            DBStringColumn::create('url'),
            DBBoolColumn::create('is_enabled'),
            DBStringColumn::create('secret'),
            DBTextColumn::create('filter_event_types'),
            DBTextColumn::create('filter_projects'),
            new DBCreatedOnByColumn(true, true),
        ]
    );
