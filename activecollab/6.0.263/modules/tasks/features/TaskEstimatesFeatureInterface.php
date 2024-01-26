<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use Angie\Features\FeatureInterface;

interface TaskEstimatesFeatureInterface extends FeatureInterface
{
    const NAME = 'task_estimates';
    const VERBOSE_NAME = 'Task Estimates';
}
