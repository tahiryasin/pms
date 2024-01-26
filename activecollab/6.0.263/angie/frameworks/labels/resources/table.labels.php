<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Labels definition table.
 *
 * @package angie.frameworks.labels
 * @subpackage resources
 */

return DB::createTable('labels')->addColumns([
    new DBIdColumn(),
    DBTypeColumn::create('Label'),
    DBNameColumn::create(255, true, 'type'),
    DBStringColumn::create('color', 50),
    new DBUpdatedOnColumn(),
    DBBoolColumn::create('is_default'),
    DBIntegerColumn::create('position', DBColumn::NORMAL, 0)->setUnsigned(true),
])->addIndices([
    DBIndex::create('position'),
]);
