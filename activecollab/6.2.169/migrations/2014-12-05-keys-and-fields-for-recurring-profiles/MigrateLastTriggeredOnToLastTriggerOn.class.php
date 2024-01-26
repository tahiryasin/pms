<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Rename last triggered on to last trigger on.
 *
 * @package ActiveCollab.migrations
 */
class MigrateLastTriggeredOnToLastTriggerOn extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->useTableForAlter('recurring_profiles')->alterColumn('last_triggered_on', DBDateColumn::create('last_trigger_on'));
        $this->doneUsingTables();
    }
}
