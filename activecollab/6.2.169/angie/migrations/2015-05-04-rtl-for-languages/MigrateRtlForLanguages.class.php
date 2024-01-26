<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add is_rtl field to languages table.
 *
 * @package angie.migrations
 */
class MigrateRtlForLanguages extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->useTableForAlter('languages')->addColumn(DBBoolColumn::create('is_rtl'), 'thousands_separator');
        $this->doneUsingTables();
    }
}
