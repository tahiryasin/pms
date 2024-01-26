<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Foundation\App\AccountId\AccountIdResolverInterface;
use ActiveCollab\Module\Tracking\Services\StopwatchService;
use ActiveCollab\Module\Tracking\Services\StopwatchServiceInterface;
use ActiveCollab\Module\Tracking\Services\TrackingService;
use ActiveCollab\Module\Tracking\Services\TrackingServiceInterface;
use ActiveCollab\Module\Tracking\Utils\BudgetNotificationsMaintenance;
use ActiveCollab\Module\Tracking\Utils\BudgetNotificationsMaintenanceInterface;
use ActiveCollab\Module\Tracking\Utils\BudgetNotificationsMaintenanceRunner;
use ActiveCollab\Module\Tracking\Utils\BudgetNotificationsMaintenanceRunnerInterface;
use ActiveCollab\Module\Tracking\Utils\BudgetNotificationsManager;
use ActiveCollab\Module\Tracking\Utils\BudgetNotificationsManagerInterface;
use ActiveCollab\Module\Tracking\Utils\StopwatchesMaintenance;
use ActiveCollab\Module\Tracking\Utils\StopwatchesMaintenanceInterface;
use ActiveCollab\Module\Tracking\Utils\StopwatchMaintenanceRunner;
use ActiveCollab\Module\Tracking\Utils\StopwatchMaintenanceRunnerInterface;
use ActiveCollab\Module\Tracking\Utils\StopwatchManager;
use ActiveCollab\Module\Tracking\Utils\StopwatchManagerInterface;
use ActiveCollab\Module\Tracking\Utils\TimeRecordSourceResolver\TimeRecordSourceResolver;
use ActiveCollab\Module\Tracking\Utils\TimeRecordSourceResolver\TimeRecordSourceResolverInterface;
use ActiveCollab\Module\Tracking\Utils\TrackingBillableStatusResolver\TrackingBillableStatusResolver;
use ActiveCollab\Module\Tracking\Utils\TrackingBillableStatusResolver\TrackingBillableStatusResolverInterface;
use Angie\Utils\OnDemandStatus\OnDemandStatusInterface;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    TimeRecordSourceResolverInterface::class => get(TimeRecordSourceResolver::class),
    StopwatchManagerInterface::class => get(StopwatchManager::class),
    StopwatchServiceInterface::class => function (ContainerInterface $container) {
        return new StopwatchService(
            AngieApplication::eventsDispatcher(),
            $container->get(StopwatchManagerInterface::class),
            new DateTimeValue()
        );
    },
    StopwatchesMaintenanceInterface::class => get(StopwatchesMaintenance::class),
    StopwatchMaintenanceRunnerInterface::class => get(StopwatchMaintenanceRunner::class),
    BudgetNotificationsManagerInterface::class => get(BudgetNotificationsManager::class),
    BudgetNotificationsMaintenanceInterface::class => function (ContainerInterface $container) {
        return new BudgetNotificationsMaintenance(
            $container->get(BudgetNotificationsManagerInterface::class),
            AngieApplication::jobs(),
            $container->get(AccountIdResolverInterface::class),
            $container->get(OnDemandStatusInterface::class)
        );
    },
    BudgetNotificationsMaintenanceRunnerInterface::class => get(BudgetNotificationsMaintenanceRunner::class),
    TrackingServiceInterface::class => function () {
        return new TrackingService(
            new DateTimeValue()
        );
    },
    TrackingBillableStatusResolverInterface::class => get(TrackingBillableStatusResolver::class),
];
