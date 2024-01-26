<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Module\OnDemand\Models\Pricing\PerSeat2018\AddOn\GetPaidAddOnInterface;
use Angie\Features\Feature;

class TaskEstimatesFeature extends Feature implements TaskEstimatesFeatureInterface
{
    public function getName(): string
    {
        return TaskEstimatesFeatureInterface::NAME;
    }

    public function getVerbose(): string
    {
        return TaskEstimatesFeatureInterface::VERBOSE_NAME;
    }

    public function getAddOnsAvailableOn(): array
    {
        return [GetPaidAddOnInterface::NAME];
    }

    public function getIsEnabledFlag(): string
    {
        return 'task_estimates_enabled';
    }

    public function getIsEnabledLockFlag(): string
    {
        return 'task_estimates_enabled_lock';
    }
}
