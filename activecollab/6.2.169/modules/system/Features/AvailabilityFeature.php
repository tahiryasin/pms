<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Features;

use ActiveCollab\Module\OnDemand\Models\Pricing\PerSeat2018\AddOn\GetPaidAddOnInterface;
use Angie\Features\Feature;

class AvailabilityFeature extends Feature implements AvailabilityFeatureInterface
{
    public function getName(): string
    {
        return AvailabilityFeatureInterface::NAME;
    }

    public function getVerbose(): string
    {
        return AvailabilityFeatureInterface::VERBOSE_NAME;
    }

    public function getAddOnsAvailableOn(): array
    {
        return [GetPaidAddOnInterface::NAME];
    }

    public function getIsEnabledFlag(): string
    {
        return 'availability_enabled';
    }

    public function getIsEnabledLockFlag(): string
    {
        return 'availability_enabled_lock';
    }
}
