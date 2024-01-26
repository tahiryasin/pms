<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Action on by composite column definition.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBActionOnByColumn extends DBCompositeColumn
{
    /**
     * Action name.
     *
     * @var string
     */
    protected $action;

    /**
     * Set key on date column.
     *
     * @var bool
     */
    protected $key_on_date;

    /**
     * Set key on user ID column.
     *
     * @var bool
     */
    protected $key_on_by;

    /**
     * Construct action on by composite column.
     *
     * @param string $action
     * @param bool   $key_on_date
     * @param bool   $key_on_by
     */
    public function __construct($action, $key_on_date = false, $key_on_by = false)
    {
        $this->action = $action;
        $this->key_on_date = $key_on_date;
        $this->key_on_by = $key_on_by;

        $this->columns = [
            DBDateTimeColumn::create($this->action . '_on'),
            DBIntegerColumn::create($this->action . '_by_id', DBColumn::NORMAL)->setUnsigned(true),
            DBStringColumn::create($this->action . '_by_name', 100),
            DBStringColumn::create($this->action . '_by_email', 150),
        ];
    }

    /**
     * Create and return instance of action on by composite column.
     *
     * @param  string             $action
     * @param  bool               $key_on_date
     * @param  bool               $key_on_by
     * @return DBActionOnByColumn
     */
    public static function create($action, $key_on_date = false, $key_on_by = false)
    {
        return new self($action, $key_on_date, $key_on_by);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->key_on_date) {
            $this->table->addIndex(new DBIndex($this->action . '_on', DBIndex::KEY, $this->action . '_on'));
        }

        if ($this->key_on_by) {
            $this->table->addIndex(new DBIndex($this->action . '_by_id', DBIndex::KEY, $this->action . '_by_id'));
        }

        parent::addedToTable();
    }
}
