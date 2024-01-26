<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * IPv6 friendly IP address column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBIpAddressColumn extends DBStringColumn
{
    /**
     * Construct IP address column.
     *
     * @param string $name
     * @param mixed  $default
     */
    public function __construct($name, $default = null)
    {
        parent::__construct($name, $default);

        $this->setLength(45);
    }
}
