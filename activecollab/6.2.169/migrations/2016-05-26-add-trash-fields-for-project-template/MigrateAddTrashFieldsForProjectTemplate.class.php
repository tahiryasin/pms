<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddTrashFieldsForProjectTemplate extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $project_templates = $this->useTableForAlter('project_templates');

        if (!$project_templates->getColumn('is_trashed')) {
            $project_templates->addColumn(DBIntegerColumn::create('is_trashed', 1, 0)->setUnsigned(true));
        }

        if (!$project_templates->getColumn('trashed_on')) {
            $project_templates->addColumn(DBDateTimeColumn::create('trashed_on', null));
        }

        if (!$project_templates->getColumn('trashed_by_id')) {
            $project_templates->addColumn(DBIntegerColumn::create('trashed_by_id', 10, 0)->setUnsigned(true));
        }
    }
}
