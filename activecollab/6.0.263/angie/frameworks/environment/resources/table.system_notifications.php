<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * System Notifications table definition.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('system_notifications')->addColumns([
    new DBIdColumn(),
    DBTypeColumn::create(),
    DBFkColumn::create('recipient_id', 0, true),
    DBDateTimeColumn::create('created_on'),
    DBBoolColumn::create('is_dismissed', false),
    new DBAdditionalPropertiesColumn(),
]);
