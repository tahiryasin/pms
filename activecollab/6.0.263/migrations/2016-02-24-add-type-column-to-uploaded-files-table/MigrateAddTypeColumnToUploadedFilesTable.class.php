<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddTypeColumnToUploadedFilesTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->useTableForAlter('uploaded_files')->addColumn(DBTypeColumn::create('LocalUploadedFile'), 'id');
    }
}
