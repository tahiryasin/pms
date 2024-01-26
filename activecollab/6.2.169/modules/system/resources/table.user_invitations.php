<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * User invitations table definition.
 *
 * @package angie.frameworks.authentication
 * @subpackage resources
 */

return DB::createTable('user_invitations')->addColumns([
    new DBIdColumn(),
    DBIntegerColumn::create('user_id', 10, '0')->setUnsigned(true),
    DBRelatedObjectColumn::create('invited_to', false),
    DBStringColumn::create('code', 20, ''),
    new DBCreatedOnByColumn(),
    new DBUpdatedOnColumn(),
])->addIndices([
    DBIndex::create('user_id', DBIndex::UNIQUE),
]);
