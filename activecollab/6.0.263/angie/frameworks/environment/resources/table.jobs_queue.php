<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Jobs queue table definition.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('jobs_queue')->addColumns([
    (new DBIdColumn())
        ->setSize(DBColumn::BIG),
    DBTypeColumn::create('ApplicationObject', 191),
    DBStringColumn::create('channel', DBStringColumn::MAX_LENGTH, 'main'),
    DBIntegerColumn::create('batch_id', 10)->setUnsigned(true),
    DBIntegerColumn::create('instance_id', 10, 0)->setUnsigned(true),
    DBIntegerColumn::create('priority')->setUnsigned(true),
    DBTextColumn::create('data')->setSize(DBTextColumn::BIG),
    DBDateTimeColumn::create('available_at'),
    DBStringColumn::create('reservation_key', 40),
    DBDateTimeColumn::create('reserved_at'),
    DBIntegerColumn::create('attempts', DBColumn::SMALL)->setUnsigned(true),
    DBIntegerColumn::create('process_id', 10, 0)->setUnsigned(true),
])->addIndices([
    DBIndex::create('instance_id'),
    DBIndex::create('batch_id'),
    DBIndex::create('channel'),
    DBIndex::create('reservation_key', DBIndex::UNIQUE),
    DBIndex::create('priority'),
    DBIndex::create('reserved_at'),
]);
