<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric;

use Angie\Metric\Result\FlagResult;

/**
 * @package Angie\Metric
 */
abstract class Flag extends Metric implements FlagInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getClassNameSufix()
    {
        return 'Flag';
    }

    /**
     * {@inheritdoc}
     */
    protected function getResultClassName()
    {
        return FlagResult::class;
    }
}
