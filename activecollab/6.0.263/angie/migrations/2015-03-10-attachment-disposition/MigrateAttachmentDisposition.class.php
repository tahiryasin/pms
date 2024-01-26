<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add disposition field to attachments model.
 *
 * @package angie.migrations
 */
class MigrateAttachmentDisposition extends AngieModelMigration
{
    /**
     * Migreate up.
     */
    public function up()
    {
        $this->useTableForAlter('attachments')->addColumn(DBEnumColumn::create('disposition', ['attachment', 'inline'], 'attachment'), 'md5');
        $this->doneUsingTables();
    }
}
