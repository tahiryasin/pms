<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Composite column that captures info about person who updated a particular object.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBUpdatedOnByColumn extends DBActionOnByColumn
{
    /**
     * Construct action on by composite column.
     *
     * @param bool $key_on_date
     * @param bool $key_on_by
     */
    public function __construct($key_on_date = false, $key_on_by = false)
    {
        parent::__construct('updated', $key_on_date, $key_on_by);
    }

    /**
     * Trigger after this column gets added to the table.
     */
    public function addedToTable()
    {
        $this->table->addModelTrait([
            IUpdatedOn::class => IUpdatedOnImplementation::class,
            IUpdatedBy::class => IUpdatedByImplementation::class,
        ]);

        parent::addedToTable();
    }
}
