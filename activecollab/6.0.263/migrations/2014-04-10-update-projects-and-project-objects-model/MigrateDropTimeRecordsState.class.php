<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Drop state field from time records table.
 *
 * @package activeCollab.module.system
 * @subpackage migrations
 */
class MigrateDropTimeRecordsState extends AngieModelMigration
{
    /**
     * Construct migration.
     */
    public function __construct()
    {
        $this->executeAfter('MigrateTasksToNewStorage');
    }

    /**
     * Migrate up.
     */
    public function up()
    {
        $time_records = $this->useTableForAlter('time_records');

        $time_records->addColumn(DBBoolColumn::create('is_trashed'), 'position');
        $time_records->addColumn(DBBoolColumn::create('original_is_trashed'), 'is_trashed');
        $time_records->addColumn(DBDateTimeColumn::create('trashed_on'), 'is_trashed');
        $time_records->addColumn(DBFkColumn::create('trashed_by_id'), 'trashed_on');
        $time_records->addIndex(DBIndex::create('trashed_by_id'));

        // ---------------------------------------------------
        //  State
        // ---------------------------------------------------

        defined('STATE_TRASHED') or define('STATE_TRASHED', 1);

        $this->execute('UPDATE ' . $time_records->getName() . ' SET is_trashed = ?, original_is_trashed = ?, trashed_on = NOW() WHERE state = ?', true, false, STATE_TRASHED);

        $time_records->dropColumn('state');
        $time_records->dropColumn('original_state');
    }
}
