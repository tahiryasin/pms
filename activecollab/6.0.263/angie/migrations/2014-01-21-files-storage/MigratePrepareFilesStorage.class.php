<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Prepare files storage.
 *
 * @package angie.migrations
 */
class MigratePrepareFilesStorage extends AngieModelMigration
{
    /**
     * Prepare files storage.
     */
    public function up()
    {
        $attachments_table = $this->useTables('attachments')[0];

        $this->createTable('files', [
            new DBIdColumn(),
            DBTypeColumn::create('File'),
            new DBParentColumn(),
            DBIntegerColumn::create('category_id', 11)->setUnsigned(true),
            DBStateColumn::create(),
            DBIntegerColumn::create('visibility', 3, 0)->setUnsigned(true)->setSize(DBColumn::TINY),
            DBIntegerColumn::create('original_visibility', 3)->setUnsigned(true)->setSize(DBColumn::TINY),
            DBNameColumn::create(150),
            DBEnumColumn::create('kind', ['image', 'video', 'audio', 'document', 'archive', 'other']),
            DBStringColumn::create('mime_type', 255, 'application/octet-stream'),
            DBIntegerColumn::create('size', 10, 0)->setUnsigned(true),
            DBStringColumn::create('location', 50),
            DBStringColumn::create('md5', 32),
            DBBoolColumn::create('is_temporal', true),
            DBActionOnByColumn::create('created', true, true),
            DBActionOnByColumn::create('updated', true, true),
            DBIntegerColumn::create('version', DBIntegerColumn::NORMAL, 1)->setUnsigned(true),
            DBActionOnByColumn::create('last_version', true, true),
            new DBAdditionalPropertiesColumn(),
        ], [
            DBIndex::create('kind'),
            DBIndex::create('name'),
            DBIndex::create('size'),
        ]);

        if ($this->tableExists('file_versions')) {
            $file_versions = $this->useTableForAlter('file_versions');
            $file_versions->alterColumn('version_num', DBIntegerColumn::create('version', 5, 0)->setUnsigned(true));
        }

        $attachments = $this->useTableForAlter('attachments');
        $attachments->addColumn(DBBoolColumn::create('is_temporal', true), 'md5');

        $this->execute("UPDATE $attachments_table SET is_temporal = '0' WHERE parent_type IS NOT NULL AND parent_id > '0'");

        $this->doneUsingTables();
    }
}
