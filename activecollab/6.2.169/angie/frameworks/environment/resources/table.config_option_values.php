<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Config options values table.
 *
 * @package angie.frameworks.environment
 * @subpackage resources
 */

return DB::createTable('config_option_values')->addColumns([
    DBNameColumn::create(50),
    new DBParentColumn(true, false),
    DBTextColumn::create('value'),
])->addIndices([
    new DBIndexPrimary(['name', 'parent_type', 'parent_id']),
]);
