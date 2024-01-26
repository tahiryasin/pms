<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Update status tracking.
 *
 * @package activeCollab.modules.invoicing
 * @subpackage migrations
 */
class MigrateStatusFieldUpdateForInvoices extends AngieModelMigration
{
    /**
     * Execute after.
     */
    public function __construct()
    {
        $this->executeAfter('MigrateInvoicesToNewStorage');
    }

    /**
     * Migrate up.
     */
    public function up()
    {
        $invoices = $this->useTableForAlter('invoices');
        $invoices->addColumn(DBBoolColumn::create('is_canceled', false), 'closed_by_email');

        $this->execute('UPDATE ' . $invoices->getName() . ' SET is_canceled = ? WHERE status = ?', true, 3);

        $invoices->dropColumn('status');

        $this->doneUsingTables();
    }
}
