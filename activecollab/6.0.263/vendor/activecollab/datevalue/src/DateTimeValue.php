<?php

/*
 * This file is part of the Active Collab DateValue project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\DateValue;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * @package ActiveCollab\DateValue
 */
class DateTimeValue extends Carbon implements DateTimeValueInterface
{
    /**
     * Create a new DateValue instance.
     *
     * @param DateTimeInterface|string|null $time
     * @param \DateTimeZone|string          $tz
     */
    public function __construct($time = null, $tz = null)
    {
        if ($time instanceof DateTimeInterface) {
            $create_from = $time->format('Y-m-d H:i:s');
        } else {
            $create_from = $time;
        }

        if (empty($tz)) {
            $tz = 'UTC';
        }

        parent::__construct($create_from, $tz);
    }

    /**
     * @return int
     */
    public function jsonSerialize()
    {
        return $this->getTimestamp();
    }
}
