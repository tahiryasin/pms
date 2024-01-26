<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Type column definition.
 *
 * This is string column designed to store type name value in polymorh tables
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBTypeColumn extends DBStringColumn
{
    /**
     * @var bool
     */
    private $add_key = true;

    /**
     * Contruct type column instance.
     *
     * @param string $default_type
     * @param int    $length
     * @param bool   $add_key
     */
    public function __construct($default_type = 'ApplicationObject', $length = 191, $add_key = true)
    {
        parent::__construct('type', $length, $default_type);

        $this->add_key = $add_key;
    }

    /**
     * Create and return new type column instance.
     *
     * @param  string       $default_type
     * @param  int          $length
     * @param  bool         $add_key
     * @return DBTypeColumn
     */
    public static function create($default_type = 'ApplicationObject', $length = 191, $add_key = true)
    {
        return new self($default_type, $length, $add_key);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->add_key) {
            $this->table->addIndex(new DBIndex('type', DBIndex::KEY, 'type'));
        }

        parent::addedToTable();
    }
}
