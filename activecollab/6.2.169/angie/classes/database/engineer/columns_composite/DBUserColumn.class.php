<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Composite user column definition.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBUserColumn extends DBCompositeColumn
{
    /**
     * Name of the column.
     *
     * @var string
     */
    protected $name;

    /**
     * Flag if we need to add key on user ID field or not.
     *
     * @var bool
     */
    private $add_key;

    /**
     * Construct user column instance.
     *
     * @param string $name
     * @param bool   $add_key
     */
    public function __construct($name, $add_key = true)
    {
        $this->add_key = $add_key;
        $this->name = $name;

        $this->columns = [
            DBIntegerColumn::create($name . '_id', 10)->setUnsigned(true),
            DBStringColumn::create($name . '_name', 100),
            DBStringColumn::create($name . '_email', 150),
        ];
    }

    /**
     * Construct and return user column.
     *
     * @param  string       $name
     * @param  bool         $add_key
     * @return DBUserColumn
     */
    public static function create($name, $add_key = true)
    {
        return new self($name, $add_key);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->add_key) {
            $this->table->addIndex(new DBIndex($this->name . '_id', DBIndex::KEY, $this->name . '_id'));
        }

        parent::addedToTable();
    }
}
