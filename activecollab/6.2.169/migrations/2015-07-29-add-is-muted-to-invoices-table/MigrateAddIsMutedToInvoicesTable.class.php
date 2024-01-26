<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Migrate add is_muted column to invoices table.
 *
 * @package ActiveCollab.migrations
 */
class MigrateAddIsMutedToInvoicesTable extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $invoices = $this->useTableForAlter('invoices');

        if (!$invoices->getColumn('is_muted')) {
            $invoices->addColumn(DBBoolColumn::create('is_muted', false), 'is_canceled');
        }

        $this->doneUsingTables();
    }
}
