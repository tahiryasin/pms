<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Metric;

use Angie\Metric\Counter;
use DateValue;
use DB;

/**
 * @package ActiveCollab\Module\System\Metric
 */
final class NumberOfCalendarEventsCounter extends Counter
{
    /**
     * {@inheritdoc}
     */
    public function getValueFor(DateValue $date)
    {
        return $this->produceResult(
            (int) DB::executeFirstCell(
                'SELECT COUNT(id) AS "row_count" FROM calendar_events WHERE is_trashed = ?',
                false
            ),
            $date
        );
    }
}
