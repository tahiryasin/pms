<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Storage\AdapterResolver;

use ActiveCollab\JobsQueue\DispatcherInterface;
use ActiveCollab\Logger\LoggerInterface;
use Angie\Storage\Adapter\StorageAdapterInterface;
use IntegrationInterface;
use LocalFilesStorage;
use OnDemandFilesStorage;
use WarehouseIntegration;

class StorageAdapterResolver implements StorageAdapterResolverInterface
{
    private $account_id;
    private $capacity_calculator;
    private $jobs;
    private $logger;

    public function __construct(
        int $account_id,
        DispatcherInterface $jobs,
        LoggerInterface $logger
    )
    {
        $this->account_id = $account_id;
        $this->jobs = $jobs;
        $this->logger = $logger;
    }

    public function getByIntegration(IntegrationInterface $integration): StorageAdapterInterface
    {
        return $integration instanceof WarehouseIntegration && $integration->isInUse()
            ? new OnDemandFilesStorage(
                $this->account_id,
                $this->jobs,
                $integration,
                $this->logger
            )
            : new LocalFilesStorage($this->logger);
    }
}
