<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add heartbeat queue.
 *
 * @package angie.migrations
 */
class MigrateHeartbeatQueue extends AngieModelMigration
{
    /**
     * Migrate ups.
     */
    public function up()
    {
        $this->createTable(DB::createTable('heartbeat_queue')->addColumns([
            (new DBIdColumn())
                ->setSize(DBColumn::BIG),
            DBStringColumn::create('hash', 40),
            DBTextColumn::create('json')->setSize(DBColumn::BIG),
        ])->addIndices([
            DBIndex::create('hash', DBIndex::UNIQUE),
        ]));

        $this->addConfigOption('heartbeat_incoming_key');
        $this->addConfigOption('heartbeat_outgoing_backend_key');
        $this->addConfigOption('heartbeat_outgoing_frontend_key');
    }
}
