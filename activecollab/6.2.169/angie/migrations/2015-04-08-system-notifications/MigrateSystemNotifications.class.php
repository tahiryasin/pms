<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Migrate system notifications.
 *
 * @package angie.migrations
 */
class MigrateSystemNotifications extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->createTable('system_notifications', [
            new DBIdColumn(),
            DBTypeColumn::create(),
            DBFkColumn::create('recipient_id', 0, true),
            DBDateTimeColumn::create('created_on'),
            DBBoolColumn::create('is_dismissed', false),
            new DBAdditionalPropertiesColumn(),
        ]);
    }
}
