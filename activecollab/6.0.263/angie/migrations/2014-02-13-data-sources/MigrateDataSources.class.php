<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Introduce alternative user addresses.
 *
 * @package angie.migrations
 */
class MigrateDataSources extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $data_sources_table = 'data_sources';
        if (!DB::tableExists($data_sources_table)) {
            $this->createTable('data_sources', [
                new DBIdColumn(),
                DBTypeColumn::create('ApplicationObject'),
                DBNameColumn::create(50),
                new DBAdditionalPropertiesColumn(),
                new DBCreatedOnByColumn(),
                DBBoolColumn::create('is_private', false),
            ]);
        }

        $data_sources_mappings_table = 'data_source_mappings';
        if (!DB::tableExists($data_sources_mappings_table)) {
            $this->createTable('data_source_mappings', [
                new DBIdColumn(),
                DBIntegerColumn::create('project_id', 11),
                DBStringColumn::create('source_type', 50, ''),
                DBIntegerColumn::create('source_id', 11),
                DBIntegerColumn::create('parent_id', 11),
                DBStringColumn::create('parent_type', 50, ''),
                DBIntegerColumn::create('external_id', 11),
                DBStringColumn::create('external_type', 50, ''),
                new DBCreatedOnByColumn(),
            ]);
        }
    }
}
