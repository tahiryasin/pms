<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add memories table.
 *
 * @package angie.migrations
 */
class MigrateAddMemoriesTable extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        if ($this->tableExists('memories')) {
            return;
        }

        $this->createTable(DB::createTable('memories')->addColumns([
            new DBIdColumn(),
            DBStringColumn::create('key', 191, ''),
            DBTextColumn::create('value'),
            new DBUpdatedOnColumn(),
        ])->addIndices([
            DBIndex::create('key', DBIndex::UNIQUE),
        ]));
    }
}
