<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric\Result;

use DateValue;

/**
 * @package Angie\Metric\Result
 */
interface ResultInterface
{
    /**
     * Return metric name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return metric value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Return date for which the metric applies.
     *
     * @return DateValue
     */
    public function getDate();

    /**
     * Add result of this metric to the stats array.
     *
     * Result must be an array that contains old stats, plus result of this metric.
     *
     * @param  array $stats
     * @return array
     */
    public function addTo(array &$stats);
}
