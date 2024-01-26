<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Module\System\Utils\Recurrence\Interval\Factory\RecurrenceIntervalFactory;
use ActiveCollab\Module\System\Utils\Recurrence\Interval\Factory\RecurrenceIntervalFactoryInterface;
use ActiveCollab\Module\System\Utils\Storage\StorageOverusedNotifier\StorageOverusedNotifierInterface;
use ActiveCollab\Module\Tasks\Utils\GhostTasks\Resolver\GhostTasksResolver;
use ActiveCollab\Module\Tasks\Utils\GhostTasks\Resolver\GhostTasksResolverInterface;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\AssigneeFilter\AssigneeFilter;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\AssigneeFilter\AssigneeFilterInterface;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\DueDateFilter\DueDateFilter;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\DueDateFilter\DueDateFilterInterface;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\LabelFilter\LabelFilter;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\LabelFilter\LabelFilterInterface;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskListFilter\TaskListFilter;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskListFilter\TaskListFilterInterface;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskStatusFilter\TaskStatusFilter;
use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskStatusFilter\TaskStatusFilterInterface;
use ActiveCollab\Module\Tasks\Utils\RecurringTasksTrigger\RecurringTasksTrigger;
use ActiveCollab\Module\Tasks\Utils\RecurringTasksTrigger\RecurringTasksTriggerInterface;
use ActiveCollab\Module\Tasks\Utils\TaskFromRecurringTaskProducer\TaskFromRecurringTaskProducer;
use ActiveCollab\Module\Tasks\Utils\TaskFromRecurringTaskProducer\TaskFromRecurringTaskProducerInterface;
use Angie\Notifications\NotificationsInterface;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    TaskStatusFilterInterface::class => get(TaskStatusFilter::class),
    AssigneeFilterInterface::class => get(AssigneeFilter::class),
    DueDateFilterInterface::class => get(DueDateFilter::class),
    LabelFilterInterface::class => get(LabelFilter::class),
    TaskListFilterInterface::class => get(TaskListFilter::class),

    RecurrenceIntervalFactoryInterface::class => get(RecurrenceIntervalFactory::class),

    // @TODO: Remove calls to AngieApplication to resolve dependencies.
    TaskFromRecurringTaskProducerInterface::class => function (ContainerInterface $c) {
        return new TaskFromRecurringTaskProducer($c->get(NotificationsInterface::class), AngieApplication::log());
    },
    GhostTasksResolverInterface::class => get(GhostTasksResolver::class),

    // @TODO: Remove calls to AngieApplication to resolve dependencies.
    RecurringTasksTriggerInterface::class => function (ContainerInterface $container) {
        return new RecurringTasksTrigger(
            function () {
                return RecurringTasks::getRecurringTasksToTrigger();
            },
            AngieApplication::onDemandStatusResolver(),
            AngieApplication::storage(),
            $container->get(StorageOverusedNotifierInterface::class),
            AngieApplication::log()
        );
    },
];
