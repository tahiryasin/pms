<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add is community translation flag to languages table.
 *
 * @package ActiveCollab.modules.system
 */
class MigrateAddIsCommunityTranslationFlag extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->useTableForAlter('languages')->addColumn(DBBoolColumn::create('is_community_translation'), 'is_rtl');
        $this->doneUsingTables();
    }
}
