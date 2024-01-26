<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Drop state field from expenses table.
 *
 * @package activeCollab.module.system
 * @subpackage migrations
 */
class MigrateDropExpensesState extends AngieModelMigration
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
        $expenses = $this->useTableForAlter('expenses');

        $expenses->addColumn(DBBoolColumn::create('is_trashed'), 'position');
        $expenses->addColumn(DBBoolColumn::create('original_is_trashed'), 'is_trashed');
        $expenses->addColumn(DBDateTimeColumn::create('trashed_on'), 'is_trashed');
        $expenses->addColumn(DBFkColumn::create('trashed_by_id'), 'trashed_on');
        $expenses->addIndex(DBIndex::create('trashed_by_id'));

        // ---------------------------------------------------
        //  State
        // ---------------------------------------------------

        defined('STATE_TRASHED') or define('STATE_TRASHED', 1);

        $this->execute('UPDATE ' . $expenses->getName() . ' SET is_trashed = ?, original_is_trashed = ?, trashed_on = NOW() WHERE state = ?', true, false, STATE_TRASHED);

        $expenses->dropColumn('state');
        $expenses->dropColumn('original_state');
    }
}
