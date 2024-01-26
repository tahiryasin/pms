<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add email log.
 *
 * @package angie.migrations
 */
class MigrateAddEmailLog extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        if ($this->tableExists('email_log')) {
            return;
        }

        $this->createTable(DB::createTable('email_log')->addColumns([
            (new DBIdColumn())
                ->setSize(DBColumn::BIG),
            new DBParentColumn(false),
            DBStringColumn::create('sender'),
            DBStringColumn::create('recipient'),
            DBStringColumn::create('subject'),
            DBStringColumn::create('message_id'),
            DBDateTimeColumn::create('sent_on'),
        ])->addIndices([
            DBIndex::create('message_id'),
        ]));
    }
}
