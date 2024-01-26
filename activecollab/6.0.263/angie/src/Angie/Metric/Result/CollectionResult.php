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
class CollectionResult extends Result
{
    /**
     * CollectionResult constructor.
     *
     * @param string    $name
     * @param array     $value
     * @param DateValue $date
     */
    public function __construct($name, $value, DateValue $date)
    {
        if (!is_array($value) || empty($value)) {
            throw new InvalidArgumentException('Collection value must be a not-empty array.');
        }

        parent::__construct($name, $value, $date);
    }
}
