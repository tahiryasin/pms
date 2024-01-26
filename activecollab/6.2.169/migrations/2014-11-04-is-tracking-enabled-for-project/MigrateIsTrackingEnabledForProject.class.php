<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add is tracking enabled flag to projects.
 *
 * @package ActiveCollab.modules.system
 * @subpackage migrations
 */
class MigrateIsTrackingEnabledForProject extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->useTableForAlter('projects')->addColumn(DBBoolColumn::create('is_tracking_enabled', true), 'mail_to_project_code');
        $this->doneUsingTables();
    }
}
