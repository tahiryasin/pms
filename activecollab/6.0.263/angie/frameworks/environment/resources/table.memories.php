<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Memories table.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('memories')->addColumns([
    new DBIdColumn(),
    DBStringColumn::create('key', 191, ''),
    DBTextColumn::create('value')->setSize(DBTextColumn::MEDIUM),
    new DBUpdatedOnColumn(),
])->addIndices([
    DBIndex::create('key', DBIndex::UNIQUE),
]);
