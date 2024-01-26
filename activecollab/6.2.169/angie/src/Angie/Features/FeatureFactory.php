<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Features;

use ActiveCollab\EventsDispatcher\EventsDispatcherInterface;
use ActiveCollab\Module\System\Features\AvailabilityFeature;
use ActiveCollab\Module\System\Features\AvailabilityFeatureInterface;
use ActiveCollab\Module\System\Features\EstimatesFeature;
use ActiveCollab\Module\System\Features\EstimatesFeatureInterface;
use ActiveCollab\Module\System\Features\InvoicesFeature;
use ActiveCollab\Module\System\Features\InvoicesFeatureInterface;
use ActiveCollab\Module\System\Features\ProfitabilityFeature;
use ActiveCollab\Module\System\Features\ProfitabilityFeatureInterface;
use ActiveCollab\Module\System\Features\TimesheetFeature;
use ActiveCollab\Module\System\Features\TimesheetFeatureInterface;
use ActiveCollab\Module\System\Features\WorkloadFeature;
use ActiveCollab\Module\System\Features\WorkloadFeatureInterface;
use InvalidArgumentException;
use TaskEstimatesFeature;
use TaskEstimatesFeatureInterface;

final class FeatureFactory implements FeatureFactoryInterface
{
    private $dispatcher;

    public function __construct(EventsDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function makeFeature(string $feature): FeatureInterface
    {
        switch ($feature) {
            case TaskEstimatesFeatureInterface::NAME:
                return new TaskEstimatesFeature($this->dispatcher);
            case WorkloadFeatureInterface::NAME:
                return new WorkloadFeature($this->dispatcher);
            case ProfitabilityFeatureInterface::NAME:
                return new ProfitabilityFeature($this->dispatcher);
            case AvailabilityFeatureInterface::NAME:
                return new AvailabilityFeature($this->dispatcher);
            case TimesheetFeatureInterface::NAME:
                return new TimesheetFeature($this->dispatcher);
            case EstimatesFeatureInterface::NAME:
                return new EstimatesFeature($this->dispatcher);
            case InvoicesFeatureInterface::NAME:
                return new InvoicesFeature($this->dispatcher);
            default:
                throw new InvalidArgumentException("ERROR: {$feature} is not a Feature!");
        }
    }
}
