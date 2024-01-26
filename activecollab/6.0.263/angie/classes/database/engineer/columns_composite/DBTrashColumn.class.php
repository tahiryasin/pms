<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Trash column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBTrashColumn extends DBCompositeColumn
{
    /**
     * Construct trash column instance.
     *
     * Set $cascade to true in cases where model can be trashed when parent object is trashed
     *
     * @param bool $cascade
     */
    public function __construct($cascade = false)
    {
        $this->columns = [DBBoolColumn::create('is_trashed')];

        if ($cascade) {
            $this->columns[] = DBBoolColumn::create('original_is_trashed');
        }

        $this->columns[] = DBDateTimeColumn::create('trashed_on');
        $this->columns[] = DBFkColumn::create('trashed_by_id');
    }

    /**
     * Construct and return user column.
     *
     * Set $cascade to true in cases where model can be trashed when parent object is trashed
     *
     * @param  bool          $cascade
     * @return DBTrashColumn
     */
    public static function create($cascade = false)
    {
        return new self($cascade);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        $this->table->addIndex(new DBIndex('trashed_on'));
        $this->table->addIndex(new DBIndex('trashed_by_id'));

        parent::addedToTable();
    }
}
