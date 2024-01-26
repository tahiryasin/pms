<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Created on column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBCreatedOnColumn extends DBDateTimeColumn
{
    /**
     * Create new created_on field column.
     */
    public function __construct()
    {
        parent::__construct('created_on');
    }

    /**
     * Trigger after this column gets added to the table.
     */
    public function addedToTable()
    {
        $this->table->addModelTrait(ICreatedOn::class, ICreatedOnImplementation::class);

        parent::addedToTable();
    }
}
