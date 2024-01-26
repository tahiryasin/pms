<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Module\Tracking\Services\StopwatchService;
use ActiveCollab\Module\Tracking\Services\StopwatchServiceInterface;
use ActiveCollab\Module\Tracking\Utils\StopwatchManager;
use ActiveCollab\Module\Tracking\Utils\StopwatchManagerInterface;
use ActiveCollab\Module\Tracking\Utils\TimeRecordSourceResolver\TimeRecordSourceResolver;
use ActiveCollab\Module\Tracking\Utils\TimeRecordSourceResolver\TimeRecordSourceResolverInterface;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    TimeRecordSourceResolverInterface::class => function () {
        return new TimeRecordSourceResolver(
            AngieApplication::log()
        );
    },
    StopwatchManagerInterface::class => get(StopwatchManager::class),
    StopwatchServiceInterface::class => function (ContainerInterface $container) {
        return new StopwatchService(
            AngieApplication::eventsDispatcher(),
            $container->get(StopwatchManagerInterface::class),
            new DateTimeValue()
        );
    },
];
