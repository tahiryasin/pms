<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Drop project state field.
 *
 * @package activeCollab.modules.system
 * @subpackage migrations
 */
class MigrateDropProjectState extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $projects = $this->useTableForAlter('projects');

        $projects->addColumn(DBBoolColumn::create('is_trashed'), 'mail_to_project_code');
        $projects->addColumn(DBDateTimeColumn::create('trashed_on'), 'is_trashed');
        $projects->addColumn(DBFkColumn::create('trashed_by_id'), 'trashed_on');
        $projects->addIndex(DBIndex::create('trashed_by_id'));

        defined('STATE_TRASHED') or define('STATE_TRASHED', 1);

        $this->execute('UPDATE ' . $projects->getName() . ' SET is_trashed = ?, trashed_on = NOW() WHERE state = ?', true, STATE_TRASHED);

        $projects->dropColumn('state');
        $projects->dropColumn('original_state');

        $this->doneUsingTables();
    }
}
