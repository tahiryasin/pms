<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Foreign key column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBFkColumn extends DBIntegerColumn
{
    /**
     * Flag if we need to add key on related object fields or not.
     *
     * @var bool
     */
    private $add_key;

    /**
     * Create a new instance.
     *
     * @param  string            $name
     * @param  int               $default
     * @param  bool              $add_key
     * @throws InvalidParamError
     */
    public function __construct($name, $default = 0, $add_key = false)
    {
        if (!is_int($default) || $default < 0) {
            throw new InvalidParamError('default', $default, 'Default value must be an unsigned integer value');
        }

        $this->add_key = $add_key;

        parent::__construct($name, DBColumn::NORMAL, $default);

        $this->setUnsigned(true);
    }

    /**
     * Create new integer column instance.
     *
     * @param  string     $name
     * @param  mixed      $default
     * @param  bool       $add_key = false
     * @return DBFkColumn
     */
    public static function create($name, $default = 0, $add_key = false)
    {
        return new self($name, $default);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->add_key) {
            $this->table->addIndex(new DBIndex($this->name));
        }

        parent::addedToTable();
    }
}
