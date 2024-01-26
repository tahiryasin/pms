<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * ID column.
 *
 * This column is a plan integer column with predefined name, and unsinged and
 * auto_increment already set to true. Column length is easily configurable
 * via constructor or create() method parameter
 *
 * @package angie.library.database
 * @subpackage engieer
 */
class DBIdColumn extends DBIntegerColumn
{
    /**
     * Create new ID column.
     *
     * @param int|string $length
     */
    public function __construct($length = DBColumn::NORMAL)
    {
        parent::__construct('id', $length, 0);

        $this->setUnsigned(true);
        $this->setAutoIncrement(true);
    }
}
