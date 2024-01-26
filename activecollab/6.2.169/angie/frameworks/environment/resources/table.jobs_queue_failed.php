<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Failed jobs queue table definition.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('jobs_queue_failed')->addColumns([
    (new DBIdColumn())
        ->setSize(DBColumn::BIG),
    DBTypeColumn::create('ApplicationObject', 191),
    DBStringColumn::create('channel', DBStringColumn::MAX_LENGTH, 'main'),
    DBIntegerColumn::create('batch_id', 10)->setUnsigned(true),
    DBTextColumn::create('data')->setSize(DBTextColumn::BIG),
    DBDateTimeColumn::create('failed_at'),
    DBStringColumn::create('reason', DBStringColumn::MAX_LENGTH, ''),
])->addIndices([
    DBIndex::create('channel'),
    DBIndex::create('batch_id'),
    DBIndex::create('failed_at'),
]);
