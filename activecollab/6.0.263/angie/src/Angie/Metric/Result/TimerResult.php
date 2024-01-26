<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric\Result;

use DateValue;
use InvalidArgumentException;

class TimerResult extends Result
{
    /**
     * TimerResult constructor.
     *
     * @param string    $name
     * @param int       $value
     * @param DateValue $date
     */
    public function __construct($name, $value, DateValue $date)
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException('Timer value must be an integer.');
        }

        parent::__construct($name, $value, $date);
    }
}
