<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add is_global field to task labels.
 *
 * @package ActiveCollab.migrations
 */
class MigrateIsGlobalForTaskLabels extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $labels = $this->useTableForAlter('labels');

        $labels->addColumn(DBBoolColumn::create('is_global'), 'is_default');

        if (!$labels->indexExists('position')) {
            $labels->addIndex(DBIndex::create('position'));
        }

        $this->execute('UPDATE ' . $labels->getName() . ' SET is_global = ? WHERE color IS NOT NULL', true);

        $this->doneUsingTables();
    }
}
