<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

return DB::createTable('companies')
    ->addColumns(
        [
            new DBIdColumn(),
            DBNameColumn::create(100),
            DBTextColumn::create('address'),
            DBStringColumn::create('homepage_url'),
            DBStringColumn::create('phone'),
            DBTextColumn::create('note'),
            DBIntegerColumn::create('currency_id', DBIntegerColumn::NORMAL, null)->setUnsigned(true),
            DBStringColumn::create('tax_id'),
            new DBCreatedOnByColumn(),
            new DBUpdatedOnByColumn(),
            new DBArchiveColumn(false, true),
            DBTrashColumn::create(),
            DBBoolColumn::create('is_owner', false),
        ]
    )->addIndices(
        [
            DBIndex::create('name'),
        ]
    );
