<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add note field to calendar events.
 *
 * @package activeCollab.modules.system
 * @subpackage migrations
 */
class MigrateAddNoteFieldToCalendarEvents extends AngieModelMigration
{
    /**
     * Upgrade the data.
     */
    public function up()
    {
        $calendar_events = $this->useTableForAlter('calendar_events');

        $calendar_events->addColumn(DBTextColumn::create('note')->setSize(DBTextColumn::BIG), 'created_by_email');
    }
}
