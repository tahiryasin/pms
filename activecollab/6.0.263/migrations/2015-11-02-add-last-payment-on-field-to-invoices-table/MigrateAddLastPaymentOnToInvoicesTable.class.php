<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add last payment on field to invoices table.
 *
 * @package activeCollab.modules.system
 */
class MigrateAddLastPaymentOnToInvoicesTable extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $payments = $this->useTables('payments')[0];
        $invoices = $this->useTableForAlter('invoices');

        $invoices->addColumn(DBDateColumn::create('last_payment_on'), 'paid_amount');

        // update all paid invoices with date of last payment
        if ($rows = $this->execute("SELECT parent_id, MAX(paid_on) as paid_on FROM $payments WHERE parent_type = ? GROUP BY parent_id", 'Invoice')) {
            foreach ($rows as $row) {
                $this->execute("UPDATE {$invoices->getName()} SET last_payment_on = ? WHERE id = ?", $row['paid_on'], $row['parent_id']);
            }
        }

        $this->doneUsingTables();
    }
}
