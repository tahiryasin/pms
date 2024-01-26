<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Invoices and Estimates trash implementation.
 *
 * @package ActiveCollab.migrations
 */
class MigrateInvoicesAndEstimatesTrashImplementation extends AngieModelMigration
{
    /**
     * Upgrade the data.
     */
    public function up()
    {
        $invoices = $this->useTableForAlter('invoices');
        $estimates = $this->useTableForAlter('estimates');

        $invoices->addColumn(DBTrashColumn::create());
        $estimates->addColumn(DBTrashColumn::create());

        $this->doneUsingTables();
    }
}
