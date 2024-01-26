<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

return DB::createTable('projects')->addColumns(
    [
        new DBIdColumn(),
        DBFkColumn::create('template_id'),
        DBStringColumn::create('based_on_type', 50),
        DBFkColumn::create('based_on_id'),
        DBIntegerColumn::create('based_on_id', 10)->setUnsigned(true),
        DBFkColumn::create('company_id', 0, true),
        DBFkColumn::create('category_id', 0, true),
        DBFkColumn::create('label_id', 0, true),
        DBFkColumn::create('currency_id'),
        DBEnumColumn::create(
            'budget_type',
            [
                'fixed',
                'pay_as_you_go',
                'not_billable',
            ],
            'pay_as_you_go'
        ),
        (new DBMoneyColumn('budget'))
            ->setUnsigned(true),
        DBNameColumn::create(150),
        DBFkColumn::create('leader_id', 0, true),
        DBTextColumn::create('body')->setSize(DBTextColumn::BIG),
        DBActionOnByColumn::create('completed', true),
        new DBCreatedOnByColumn(true, true),
        new DBUpdatedOnByColumn(),
        DBDateTimeColumn::create('last_activity_on'),
        DBStringColumn::create('project_hash', DBStringColumn::MAX_LENGTH),
        DBBoolColumn::create('is_tracking_enabled', true),
        DBBoolColumn::create('is_billable', true),
        DBBoolColumn::create('members_can_change_billable', true),
        DBBoolColumn::create('is_client_reporting_enabled'),
        DBTrashColumn::create(),
        DBBoolColumn::create('is_sample'),
    ]
)->addIndices(
    [
        DBIndex::create('project_hash', DBIndex::UNIQUE),
    ]
);
