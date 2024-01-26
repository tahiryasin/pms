<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tracking\Metric;

use Angie\Metric\Collection;
use DateValue;
use DB;
use ITrackingObject;

/**
 * @package ActiveCollab\Module\Tracking\Metric
 */
class TimeRecordsCollection extends Collection
{
    public function getValueFor(DateValue $date)
    {
        $not_billable = 0;
        $billable = 0;
        $to_be_invoiced = 0;
        $pending_payment = 0;
        $paid = 0;
        $invoiced = DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM time_records WHERE invoice_item_id > ?', 0);

        if ($rows = DB::execute('SELECT COUNT(id) AS "row_count", billable_status FROM time_records WHERE is_trashed = ? GROUP BY billable_status', false)) {
            foreach ($rows as $row) {
                if ($row['billable_status'] == ITrackingObject::NOT_BILLABLE) {
                    $not_billable += $row['row_count'];
                } else {
                    $billable += $row['row_count'];

                    if ($row['billable_status'] == ITrackingObject::BILLABLE) {
                        $to_be_invoiced += $row['row_count'];
                    } elseif ($row['billable_status'] == ITrackingObject::PENDING_PAYMENT) {
                        $pending_payment += $row['row_count'];
                    } elseif ($row['billable_status'] == ITrackingObject::PAID) {
                        $paid += $row['row_count'];
                    }
                }
            }
        }

        return $this->produceResult(
            [
                'total' => $not_billable + $billable,
                'not_billable' => $not_billable,
                'billable' => $billable,
                'to_be_invoiced' => $to_be_invoiced,
                'pending_payment' => $pending_payment,
                'paid' => $paid,
                'invoiced' => $invoiced,
            ],
            $date
        );
    }
}
