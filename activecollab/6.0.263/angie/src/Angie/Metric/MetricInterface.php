<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric;

use Angie\Metric\Result\ResultInterface;
use DateValue;

/**
 *  @package Angie\Metric
 */
interface MetricInterface
{
    /**
     * Return short metric name (in underscore notation).
     *
     * @return string
     */
    public function getName();

    /**
     * Get value of the given metric for the given date.
     *
     * @param  DateValue       $date
     * @return ResultInterface
     */
    public function getValueFor(DateValue $date);
}
