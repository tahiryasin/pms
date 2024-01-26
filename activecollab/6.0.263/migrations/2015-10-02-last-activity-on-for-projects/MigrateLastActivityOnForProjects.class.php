<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Adds last_activity_on field on projects table.
 *
 * @package activeCollab.modules.system
 */
class MigrateLastActivityOnForProjects extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $project = $this->useTableForAlter('projects');

        if ($project->getColumn('last_activity_on') === null) {
            $project->addColumn(DBDateTimeColumn::create('last_activity_on'), 'updated_on');
        }

        $this->execute('UPDATE projects SET last_activity_on = updated_on');
        $this->doneUsingTables();
    }
}
