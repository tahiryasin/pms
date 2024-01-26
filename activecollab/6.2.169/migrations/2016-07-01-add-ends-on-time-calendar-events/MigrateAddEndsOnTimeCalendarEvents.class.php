<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddEndsOnTimeCalendarEvents extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $events = $this->useTableForAlter('calendar_events');

        $events->addColumn(DBTimeColumn::create('ends_on_time'), 'ends_on');
        $events->addIndex(DBIndex::create('ends_on_time'));

        $this->doneUsingTables();
    }
}
