<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Storage\CapacityCalculatorResolver;

use AccountSettingsInterface;
use Angie\Storage\Capacity\StorageCapacityCalculatorInterface;
use Angie\Storage\Capacity\StorageStorageCapacityCalculator;
use Angie\Utils\OnDemandStatus\OnDemandStatusInterface;

class StorageCapacityCalculatorResolver implements StorageCapacityCalculatorResolverInterface
{
    private $on_demand_status_resolver;
    private $account_settings;

    public function __construct(
        OnDemandStatusInterface $on_demand_status_resolver,
        ?AccountSettingsInterface $account_settings
    )
    {
        $this->on_demand_status_resolver = $on_demand_status_resolver;
        $this->account_settings = $account_settings;
    }

    public function getCapacityCalculator(): StorageCapacityCalculatorInterface
    {
        if ($this->on_demand_status_resolver->isOnDemand()) {
            return new StorageStorageCapacityCalculator(
                $this->account_settings->getAccountPlan()->getMaxDiskSpace(),
                5,
                php_config_value_to_bytes('100M')
            );
        } else {
            return new StorageStorageCapacityCalculator(
                0,
                0,
                0
            );
        }
    }
}
