<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Config options table.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('config_options')->addColumns([
    new DBIdColumn(),
    DBNameColumn::create(100),
    DBTextColumn::create('value'),
    new DBUpdatedOnColumn(),
])->addIndices([
    DBIndex::create('name', DBIndex::UNIQUE),
]);
