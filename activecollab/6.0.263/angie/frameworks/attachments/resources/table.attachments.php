<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Attachments table definition.
 *
 * @package angie.frameworks.attachments
 * @subpackage resources
 */

return DB::createTable('attachments')->addColumns([
    new DBIdColumn(),
    DBTypeColumn::create('Attachment'),
    new DBParentColumn(),
    DBFileMetaColumn::create(),
    DBEnumColumn::create('disposition', ['attachment', 'inline'], 'attachment'),
    new DBCreatedOnByColumn(true),
    new DBAdditionalPropertiesColumn(),
    DBTextColumn::create('search_content')->setSize(DBTextColumn::BIG),
]);
