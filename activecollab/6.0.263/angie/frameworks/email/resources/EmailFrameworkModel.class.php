<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Email framework model definition.
 *
 * @package angie.frameworks.email
 * @subpackage resources
 */
class EmailFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct environment framework model definition.
     *
     * @param EmailFramework $parent
     */
    public function __construct(EmailFramework $parent)
    {
        parent::__construct($parent);

        $this->addTable(DB::createTable('email_log')->addColumns([
            (new DBIdColumn())
                ->setSize(DBColumn::BIG),
            DBIntegerColumn::create('instance_id', 10, 0)->setUnsigned(true),
            new DBParentColumn(false),
            DBStringColumn::create('sender'),
            DBStringColumn::create('recipient'),
            DBStringColumn::create('subject'),
            DBStringColumn::create('message_id'),
            DBDateTimeColumn::create('sent_on'),
        ])->addIndices([
            DBIndex::create('instance_id'),
            DBIndex::create('sent_on'),
            DBIndex::create('message_id'),
        ]));
    }
}
