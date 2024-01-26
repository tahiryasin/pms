<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric\Result;

use DateValue;
use InvalidArgumentException;

/**
 * @package Angie\Metric\Result
 */
class FlagResult extends Result
{
    /**
     * FlagResult constructor.
     *
     * @param string    $name
     * @param bool      $value
     * @param DateValue $date
     */
    public function __construct($name, $value, DateValue $date)
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('Flag value must be boolean');
        }

        parent::__construct($name, $value, $date);
    }
}
