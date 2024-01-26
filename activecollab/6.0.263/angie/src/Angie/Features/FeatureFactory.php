<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Features;

use InvalidArgumentException;
use TaskEstimatesFeature;
use TaskEstimatesFeatureInterface;

final class FeatureFactory implements FeatureFactoryInterface
{
    public function makeFeature(string $feature): FeatureInterface
    {
        switch($feature) {
            case TaskEstimatesFeatureInterface::NAME:
                return new TaskEstimatesFeature();
            default:
                throw new InvalidArgumentException("ERROR: {$feature} is not a Feature!");
        }
    }
}
