<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add failed jobs log table.
 *
 * @package angie.migrations
 */
class MigrateFailedJobsLog extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        if ($this->tableExists('jobs_queue_failed')) {
            return;
        }

        $this->createTable(DB::createTable('jobs_queue_failed')->addColumns([
            (new DBIdColumn())
                ->setSize(DBColumn::BIG),
            DBTypeColumn::create('ApplicationObject', 191),
            DBTextColumn::create('data'),
            DBDateTimeColumn::create('failed_at'),
            DBStringColumn::create('reason', DBStringColumn::MAX_LENGTH, ''),
        ])->addIndices([
            DBIndex::create('failed_at'),
        ]));
    }
}
