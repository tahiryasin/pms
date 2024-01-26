<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Replace heartbeat queue with jobs queue.
 *
 * @package angie.migration
 */
class MigrateHeartbeatWithJobs extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->dropTable('heartbeat_queue');

        if (!$this->tableExists('jobs_queue')) {
            $this->createTable(DB::createTable('jobs_queue')->addColumns([
                (new DBIdColumn())
                    ->setSize(DBColumn::BIG),
                DBTypeColumn::create('ApplicationObject', 191),
                DBIntegerColumn::create('priority')->setUnsigned(true),
                DBTextColumn::create('data'),
                DBDateTimeColumn::create('available_at'),
                DBStringColumn::create('reservation_key', 40),
                DBDateTimeColumn::create('reserved_at'),
                DBIntegerColumn::create('attempts', 5)->setUnsigned(true),
            ])->addIndices([
                DBIndex::create('reservation_key', DBIndex::UNIQUE),
                DBIndex::create('priority'),
                DBIndex::create('reserved_at'),
            ]));
        }
    }
}
