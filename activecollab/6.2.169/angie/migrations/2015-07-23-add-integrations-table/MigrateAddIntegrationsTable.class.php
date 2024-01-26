<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add integrations table.
 *
 * @package angie.migrations
 */
class MigrateAddIntegrationsTable extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        if ($this->tableExists('integrations')) {
            return;
        }

        $this->createTable(DB::createTable('integrations')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create(),
            new DBAdditionalPropertiesColumn(),
            new DBCreatedOnByColumn(),
        ]));
    }
}
