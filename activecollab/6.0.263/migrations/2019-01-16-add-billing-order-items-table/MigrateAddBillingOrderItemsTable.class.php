<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddBillingOrderItemsTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->tableExists('billing_order_items')) {
            $this->createTable(
                DB::createTable('billing_order_items')->addColumns(
                    [
                        new DBIdColumn(),
                        DBTypeColumn::create(
                            'ActiveCollab\Module\OnDemand\Model\BillingOrderItem\SubscriptionFeeBillingOrderItem'
                        ),
                        DBIntegerColumn::create('billing_order_id')->setUnsigned(true),
                        DBIntegerColumn::create('billing_balance_record_id', 5, null),
                    ]
                )
            );
        }
    }
}
