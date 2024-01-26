<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddIsSampleToProjectTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $projects = $this->useTableForAlter('projects');
        $column_name = 'is_sample';

        if (!$projects->getColumn($column_name)) {
            $projects->addColumn(
                DBBoolColumn::create($column_name, false),
                'trashed_by_id'
            );
        }

        $this->doneUsingTables();
    }
}
