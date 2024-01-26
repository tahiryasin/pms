<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Executed model migrations.
 *
 * @package angie.frameworks.enviornment
 * @subpackage resources
 */

return DB::createTable('executed_model_migrations')->addColumns([
    (new DBIdColumn())
        ->setSize(DBColumn::SMALL),
    DBStringColumn::create('migration', DBStringColumn::MAX_LENGTH, ''),
    DBDateColumn::create('changeset_timestamp'),
    DBStringColumn::create('changeset_name', DBStringColumn::MAX_LENGTH),
    DBDateTimeColumn::create('executed_on'),
])->addIndices([
    DBIndex::create('migration', DBIndex::UNIQUE, 'migration'),
    DBIndex::create('executed_on'),
]);
