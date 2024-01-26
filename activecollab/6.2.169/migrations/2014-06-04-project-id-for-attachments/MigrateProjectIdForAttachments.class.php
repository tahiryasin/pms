<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add project_id and is_hidden_from_clients field to attachments table.
 *
 * @package activeCollab.modules.system
 * @subpackage models
 */
class MigrateProjectIdForAttachments extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $attachments = $this->useTableForAlter('attachments');

        $attachments->addColumn(DBIntegerColumn::create('project_id', 10, 0)->setUnsigned(true), 'id');
        $attachments->addColumn(DBBoolColumn::create('is_hidden_from_clients'), 'project_id');

        $attachments->addIndex(DBIndex::create('project_id'));

        $this->doneUsingTables();
    }
}
