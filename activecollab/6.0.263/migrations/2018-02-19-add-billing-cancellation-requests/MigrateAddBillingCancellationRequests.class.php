<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddBillingCancellationRequests extends AngieModelMigration
{
    public function up()
    {
        if (!$this->tableExists('billing_cancellation_requests')) {
            $this->createTable(
                DB::createTable('billing_cancellation_requests')->addColumns(
                    [
                        new DBIdColumn(),
                        DBStringColumn::create('hash', 50),
                        DBTextColumn::create('feedback'),
                        DBEnumColumn::create('status', ['created', 'feedback', 'password_verified', 'confirmed']),
                        DBDateTimeColumn::create('expired_at'),
                        new DBCreatedOnByColumn(),
                        new DBCreatedOnColumn(),
                        new DBUpdatedOnColumn(),
                    ]
                )->addIndices(
                    [
                        DBIndex::create('hash', DBIndex::UNIQUE),
                    ]
                )
            );
        }
    }
}
