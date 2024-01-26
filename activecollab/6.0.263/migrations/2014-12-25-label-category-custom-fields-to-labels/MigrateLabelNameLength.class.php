<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Extend label name length.
 *
 * @package angie.migrations
 */
class MigrateLabelNameLength extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $labels = $this->useTableForAlter('labels');

        $this->execute('ALTER TABLE ' . $labels->getName() . ' CHARACTER SET = utf8mb4');
        $labels->alterColumn('name', DBNameColumn::create(191, true, 'type'));

        $this->doneUsingTables();
    }
}
