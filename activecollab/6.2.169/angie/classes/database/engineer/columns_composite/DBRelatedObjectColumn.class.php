<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Related object composite column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBRelatedObjectColumn extends DBCompositeColumn
{
    /**
     * Name of the relation.
     *
     * @var string
     */
    private $relation_name;

    /**
     * Flag if we need to add key on related object fields or not.
     *
     * @var bool
     */
    private $add_key;

    /**
     * Construct related object column instance.
     *
     * @param string $relation_name
     * @param bool   $add_key
     * @param bool   $can_be_null
     */
    public function __construct($relation_name, $add_key = true, $can_be_null = true)
    {
        $this->relation_name = $relation_name;
        $this->add_key = $add_key;

        $this->columns = [
            DBStringColumn::create("{$relation_name}_type", 50, ($can_be_null ? null : '')),
            DBIntegerColumn::create("{$relation_name}_id", DBColumn::NORMAL, ($can_be_null ? null : 0))->setUnsigned(true),
        ];
    }

    /**
     * Construct and return related object column.
     *
     * @param  string                $relation_name
     * @param  bool                  $add_key
     * @param  bool                  $can_be_null
     * @return DBRelatedObjectColumn
     */
    public static function create($relation_name, $add_key = true, $can_be_null = true)
    {
        return new self($relation_name, $add_key, $can_be_null);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->add_key) {
            $this->table->addIndex(new DBIndex($this->relation_name, DBIndex::KEY, ["{$this->relation_name}_type", "{$this->relation_name}_id"]));
        }

        parent::addedToTable();
    }
}
