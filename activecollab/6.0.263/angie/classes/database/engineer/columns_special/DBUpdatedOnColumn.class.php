<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Updated on column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBUpdatedOnColumn extends DBDateTimeColumn
{
    /**
     * Create new updated_on field column.
     */
    public function __construct()
    {
        parent::__construct('updated_on');
    }

    /**
     * Trigger after this column gets added to the table.
     */
    public function addedToTable()
    {
        $this->table->addModelTrait(IUpdatedOn::class, IUpdatedOnImplementation::class);

        parent::addedToTable();
    }
}
