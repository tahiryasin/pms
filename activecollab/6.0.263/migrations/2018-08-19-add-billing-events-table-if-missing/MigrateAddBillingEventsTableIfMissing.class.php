<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddBillingEventsTableIfMissing extends AngieModelMigration
{
    public function up()
    {
        if (!$this->tableExists('billing_events')) {
            $this->createTable(
                DB::createTable('billing_events')->addColumns(
                    [
                        new DBIdColumn(),
                        DBTypeColumn::create(),
                        DBTextColumn::create('payload'),
                        DBDateTimeColumn::create('timestamp'),
                    ]
                )
            );
        }
    }
}
