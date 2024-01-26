<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddAvailabilityRecordModel extends AngieModelMigration
{
    public function up()
    {
        if (!$this->tableExists('availability_records')) {
            $this->createTable(
                DB::createTable('availability_records')->addColumns(
                    [
                        new DBIdColumn(),
                        DBIntegerColumn::create('availability_type_id', 10, 0)->setUnsigned(true),
                        DBIntegerColumn::create('user_id', 10, 0)->setUnsigned(true),
                        DBStringColumn::create('message', 255),
                        DBDateColumn::create('start_date'),
                        DBDateColumn::create('end_date'),
                        new DBCreatedOnByColumn(),
                        new DBUpdatedOnColumn(),
                    ]
                )->addIndices(
                    [
                        DBIndex::create('availability_type_id'),
                        DBIndex::create('user_id'),
                    ]
                )
            );
        }
    }
}
