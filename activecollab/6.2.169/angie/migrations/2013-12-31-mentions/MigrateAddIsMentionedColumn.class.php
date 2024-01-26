<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add is_mentioned migration column.
 *
 * @package angie.migrations
 */
class MigrateAddIsMentionedColumn extends AngieModelMigration
{
    /**
     * Up the database.
     */
    public function up()
    {
        $this->loadTable('notification_recipients')->addColumn(DBBoolColumn::create('is_mentioned', false));
    }
}
