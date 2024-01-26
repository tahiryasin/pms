<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add uploaded files table.
 *
 * @package angie.migrations
 */
class MigrateAddUploadedFiles extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->createTable(DB::createTable('uploaded_files')->addColumns([
            new DBIdColumn(),
            DBFileMetaColumn::create(),
            DBStringColumn::create('code', 40),
            new DBCreatedOnByColumn(true),
        ])->addIndices([
            DBIndex::create('code', DBIndex::UNIQUE),
        ]));
    }
}
