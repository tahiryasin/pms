<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add is client reporting enabled flag to projects.
 *
 * @package ActiveCollab.migrations
 */
class MigrateClientReportingForProjects extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->useTableForAlter('projects')->addColumn(DBBoolColumn::create('is_client_reporting_enabled'), 'is_tracking_enabled');
        $this->doneUsingTables();
    }
}
