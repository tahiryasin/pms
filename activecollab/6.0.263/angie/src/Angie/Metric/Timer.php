<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric;

use Angie\Metric\Result\TimerResult;

abstract class Timer extends Metric implements TimerInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getClassNameSufix()
    {
        return 'Timer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResultClassName()
    {
        return TimerResult::class;
    }
}
