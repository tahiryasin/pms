<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add support for multiple days off.
 *
 * @package angie.migrations
 */
class MigrateAddSupportForMultipleDaysOff extends AngieModelMigration
{
    /**
     * Up the database.
     */
    public function up()
    {
        $table = $this->useTableForAlter('day_offs');

        if ($table->indexExists('day_off_name')) {
            $table->dropIndex('day_off_name');
        }

        $table->addColumn(DBDateColumn::create('end_date'), 'event_date');
        $table->alterColumn('event_date', DBDateColumn::create('start_date'));

        $table->addIndex(DBIndex::create('day_off_name', DBIndex::UNIQUE, ['name', 'start_date', 'end_date']));

        $this->execute('UPDATE ' . $table->getName() . ' SET end_date = start_date');

        $this->doneUsingTables();
    }
}
