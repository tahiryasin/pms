<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Feather specific notifications implementation, take 2.
 *
 * @package angie.migrations
 */
class MigrateNotificationsForFeatherTake2 extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->removeConfigOption('notifications_fetched_on');

        $this->useTableForAlter('notification_recipients')->addColumn(DBDateTimeColumn::create('read_on'), 'recipient_email');
        $this->doneUsingTables();
    }
}
