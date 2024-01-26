<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Features;

use ActiveCollab\Module\OnDemand\Models\Pricing\PerSeat2018\AddOn\GetPaidAddOnInterface;
use Angie\Features\Feature;

class TimesheetFeature extends Feature implements TimesheetFeatureInterface
{
    public function getName(): string
    {
        return TimesheetFeatureInterface::NAME;
    }

    public function getVerbose(): string
    {
        return TimesheetFeatureInterface::VERBOSE_NAME;
    }

    public function getAddOnsAvailableOn(): array
    {
        return [GetPaidAddOnInterface::NAME];
    }

    public function getIsEnabledFlag(): string
    {
        return 'timesheet_enabled';
    }

    public function getIsEnabledLockFlag(): string
    {
        return 'timesheet_enabled_lock';
    }
}
