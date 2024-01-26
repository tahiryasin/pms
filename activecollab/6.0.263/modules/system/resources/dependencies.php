<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use ActiveCollab\Foundation\App\RootUrl\RootUrlInterface as RootUrlInterfaceAlias;
use ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\Links\TextReplacement\Resolver\TextReplacementResolverInterface;
use ActiveCollab\Foundation\Urls\IgnoredDomainsResolver\IgnoredDomainsResolverInterface;
use ActiveCollab\Foundation\Urls\Router\RouterInterface;
use ActiveCollab\Foundation\Wrappers\DataObjectPool\DataObjectPoolInterface;
use ActiveCollab\Module\System\Utils\BodyProcessorResolver\BodyProcessorResolver;
use ActiveCollab\Module\System\Utils\BodyProcessorResolver\BodyProcessorResolverInterface;
use ActiveCollab\Module\System\Utils\Dependency\ProjectTemplateDependencyResolver;
use ActiveCollab\Module\System\Utils\Dependency\ProjectTemplateDependencyResolverInterface;
use ActiveCollab\Module\System\Utils\InlineImageDetailsResolver\InlineImageDetailsResolver;
use ActiveCollab\Module\System\Utils\InlineImageDetailsResolver\InlineImageDetailsResolverInterface;
use ActiveCollab\Module\System\Utils\ProjectTemplateDuplicator\ProjectTemplateDuplicator;
use ActiveCollab\Module\System\Utils\ProjectTemplateDuplicator\ProjectTemplateDuplicatorInterface;
use ActiveCollab\Module\System\Utils\Storage\StorageOverusedNotifier\StorageOverusedNotifier;
use ActiveCollab\Module\System\Utils\Storage\StorageOverusedNotifier\StorageOverusedNotifierInterface;
use Angie\Memories\MemoriesWrapperInterface;
use Angie\Notifications\NotificationsInterface;
use function DI\get;
use Psr\Container\ContainerInterface;

return [
    InlineImageDetailsResolverInterface::class => get(InlineImageDetailsResolver::class),
    ProjectTemplateDuplicatorInterface::class => get(ProjectTemplateDuplicator::class),
    ProjectTemplateDependencyResolverInterface::class => function (ContainerInterface $c) {
        return new ProjectTemplateDependencyResolver(AngieApplication::authentication()->getAuthenticatedUser());
    },

    // @TODO: Remove dependencies that AngieApplication provides.
    BodyProcessorResolverInterface::class => function (ContainerInterface $c) {
        return new BodyProcessorResolver(
            $c->get(DataObjectPoolInterface::class),
            AngieApplication::authentication(),
            $c->get(RouterInterface::class),
            $c->get(RouterInterface::class),
            $c->get(InlineImageDetailsResolverInterface::class),
            $c->get(TextReplacementResolverInterface::class),
            $c->get(IgnoredDomainsResolverInterface::class),
            $c->get(RootUrlInterfaceAlias::class),
            AngieApplication::log()
        );
    },

    // @TODO: Remove dependencies that AngieApplication provides.
    StorageOverusedNotifierInterface::class => function (ContainerInterface $container) {
        return new StorageOverusedNotifier(
            $container->get(NotificationsInterface::class),
            $container->get(MemoriesWrapperInterface::class),
            AngieApplication::storageCapacityCalculator(),
            $container->get(CurrentTimestampInterface::class),
            AngieApplication::log()
        );
    },
];
