<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * State column definition.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBStateColumn extends DBCompositeColumn
{
    /**
     * Construct state column instance.
     *
     * @param int $default
     */
    public function __construct($default = 0)
    {
        $this->columns = [
            DBIntegerColumn::create('state', 3, $default)->setUnsigned(true)->setSize(DBColumn::TINY),
            DBIntegerColumn::create('original_state', 3)->setUnsigned(true)->setSize(DBColumn::TINY),
        ];
    }

    /**
     * Construct and return state column.
     *
     * @param  int           $default
     * @return DBStateColumn
     */
    public static function create($default = 0)
    {
        return new self($default);
    }
}
