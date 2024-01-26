<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric;

use Angie\Metric\Result\CounterResult;

/**
 * @package Angie\Metric
 */
abstract class Counter extends Metric implements CounterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getClassNameSufix()
    {
        return 'Counter';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResultClassName()
    {
        return CounterResult::class;
    }
}
