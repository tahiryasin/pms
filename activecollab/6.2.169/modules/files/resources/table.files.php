<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Stored files table.
 *
 * @package angie.frameworks.attachments
 * @subpackage resources
 */

return DB::createTable('files')->addColumns([
    new DBIdColumn(),
    DBTypeColumn::create('File'),
    DBIntegerColumn::create('project_id', DBColumn::NORMAL, 0)->setUnsigned(true),
    DBFileMetaColumn::create(),
    DBBoolColumn::create('is_hidden_from_clients'),
    DBTrashColumn::create(true),
    new DBCreatedOnByColumn(true, true),
    new DBUpdatedOnByColumn(true, true),
    new DBAdditionalPropertiesColumn(),
    DBTextColumn::create('search_content')->setSize(DBTextColumn::BIG),
])->addIndices([
    DBIndex::create('project_id'),
    DBIndex::create('name'),
]);
