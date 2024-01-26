<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * History framework model definition.
 *
 * @package angie.frameworks.history
 * @subpackage resources
 */
class HistoryFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct history framework model definition.
     *
     * @param HistoryFramework $parent
     */
    public function __construct(HistoryFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(DB::createTable('modification_logs')->addColumns([
            (new DBIdColumn())
                ->setSize(DBColumn::BIG),
            new DBParentColumn(),
            new DBCreatedOnByColumn(true),
        ]))->setOrderBy('created_on');

        $this->addTable(DB::createTable('modification_log_values')->addColumns([
            DBIntegerColumn::create('modification_id', DBColumn::NORMAL, 0)->setUnsigned(true),
            DBStringColumn::create('field', 50, ''),
            DBTextColumn::create('old_value')->setSize(DBColumn::BIG),
            DBTextColumn::create('new_value')->setSize(DBColumn::BIG),
        ])->addIndices([
            new DBIndexPrimary(['modification_id', 'field']),
        ]));
    }
}
