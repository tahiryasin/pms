<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Drop calendar state field.
 *
 * @package activeCollab.modules.system
 * @subpackage migrations
 */
class MigrateAddFieldOriginalIsTrashedToCalendarEvents extends AngieModelMigration
{
    /**
     * Upgrade the data.
     */
    public function up()
    {
        $calendar_events = $this->useTableForAlter('calendar_events');

        $calendar_events->addColumn(DBBoolColumn::create('original_is_trashed'), 'is_trashed');

        $this->execute('UPDATE ' . $calendar_events->getName() . ' SET original_is_trashed = ?', false);
    }
}
