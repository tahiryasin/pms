<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Utils;

use ActiveCollab\ActiveCollabJobs\Jobs\Instance\BudgetNotificationMaintenanceJob;
use ActiveCollab\ActiveCollabJobs\Jobs\Instance\Job;
use ActiveCollab\Foundation\App\AccountId\AccountIdResolverInterface;
use ActiveCollab\JobsQueue\DispatcherInterface;
use Angie\Utils\OnDemandStatus\OnDemandStatusInterface;
use DateTimeValue;
use SystemModule;

class BudgetNotificationsMaintenance implements BudgetNotificationsMaintenanceInterface
{
    private $manager;
    private $jobs_dispatcher;
    private $account_id_resolver;
    private $on_demand_status;

    public function __construct(
        BudgetNotificationsManagerInterface $manager,
        DispatcherInterface $jobs_dispatcher,
        AccountIdResolverInterface $account_id_resolver,
        OnDemandStatusInterface $on_demand_status
    )
    {
        $this->manager = $manager;
        $this->jobs_dispatcher = $jobs_dispatcher;
        $this->account_id_resolver = $account_id_resolver;
        $this->on_demand_status = $on_demand_status;
    }

    public function run(): void
    {
        if (count($this->manager->getProjectsIds()) > 0) {
            $this->jobs_dispatcher->dispatch(
                new BudgetNotificationMaintenanceJob(
                    [
                        'priority' => Job::HAS_HIGHEST_PRIORITY,
                        'instance_id' => $this->account_id_resolver->getAccountId(),
                        'instance_type' => Job::FEATHER,
                        'date' => DateTimeValue::now()->format('Y-m-d'),
                        'attempts' => 3,
                        'ondemand' => $this->on_demand_status->isOnDemand(),
                    ]
                ),
                SystemModule::MAINTENANCE_JOBS_QUEUE_CHANNEL
            );
        }
    }
}
