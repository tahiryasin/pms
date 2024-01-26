<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Body column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBBodyColumn extends DBTextColumn
{
    /**
     * @var bool
     */
    private $add_body_trait;

    /**
     * Create new created_on field column.
     *
     * @param bool $add_body_trait
     */
    public function __construct($add_body_trait = true)
    {
        parent::__construct('body');

        $this->setSize(DBTextColumn::BIG);
        $this->add_body_trait = (bool) $add_body_trait;
    }

    /**
     * Create new created on column instance.
     *
     * @param  bool         $add_body_trait
     * @return DBBodyColumn
     */
    public static function create($add_body_trait = true)
    {
        return new self($add_body_trait);
    }

    /**
     * Trigger after this column gets added to the table.
     */
    public function addedToTable()
    {
        if ($this->add_body_trait) {
            $this->table->addModelTrait('IBody', 'IBodyImplementation');
        }

        parent::addedToTable();
    }
}
