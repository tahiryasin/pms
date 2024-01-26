<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric;

use Angie\Metric\Result\CollectionResult;

/**
 * @package Angie\Metric
 */
abstract class Collection extends Metric implements CollectionInterface
{
    protected function getClassNameSufix()
    {
        return 'Collection';
    }

    protected function getResultClassName()
    {
        return CollectionResult::class;
    }
}
