<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Decimal column tailered for storing money amounts.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBMoneyColumn extends DBDecimalColumn
{
    /**
     * Construct decimal column.
     *
     * @param string $name
     * @param mixed  $default
     */
    public function __construct($name, $default = null)
    {
        parent::__construct($name, 13, 3, $default);
    }
}
