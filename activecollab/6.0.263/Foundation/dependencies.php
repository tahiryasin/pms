<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation;

use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use ActiveCollab\Foundation\App\Mode\ApplicationMode;
use ActiveCollab\Foundation\App\Mode\ApplicationModeInterface;
use ActiveCollab\Foundation\App\RootUrl\RootUrl;
use ActiveCollab\Foundation\App\RootUrl\RootUrlInterface;
use ActiveCollab\Foundation\Compile\CompiledUrlAssembler;
use ActiveCollab\Foundation\Compile\CompiledUrlMatcher;
use ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\Links\TextReplacement\Resolver\TextReplacementResolver;
use ActiveCollab\Foundation\Text\BodyProcessor\TagProcessor\Links\TextReplacement\Resolver\TextReplacementResolverInterface;
use ActiveCollab\Foundation\Text\HtmlToDomConverter\HtmlToDomConverter;
use ActiveCollab\Foundation\Text\HtmlToDomConverter\HtmlToDomConverterInterface;
use ActiveCollab\Foundation\Urls\IgnoredDomainsResolver\IgnoredDomainsResolver;
use ActiveCollab\Foundation\Urls\IgnoredDomainsResolver\IgnoredDomainsResolverInterface;
use ActiveCollab\Foundation\Urls\Router\Mapper\Factory\RouteMapperFactory;
use ActiveCollab\Foundation\Urls\Router\Mapper\Factory\RouteMapperFactoryInterface;
use ActiveCollab\Foundation\Urls\Router\Mapper\RouteMapperInterface;
use ActiveCollab\Foundation\Urls\Router\Router;
use ActiveCollab\Foundation\Urls\Router\RouterInterface;
use ActiveCollab\Foundation\Urls\Router\UrlAssembler\LiveUrlAssembler;
use ActiveCollab\Foundation\Urls\Router\UrlAssembler\UrlAssemblerInterface;
use ActiveCollab\Foundation\Urls\Router\UrlMatcher\LiveUrlMatcher;
use ActiveCollab\Foundation\Urls\Router\UrlMatcher\UrlMatcherInterface;
use ActiveCollab\Foundation\Wrappers\ConfigOptions\ConfigOptions;
use ActiveCollab\Foundation\Wrappers\ConfigOptions\ConfigOptionsInterface;
use ActiveCollab\Foundation\Wrappers\DataObjectPool\DataObjectPool;
use ActiveCollab\Foundation\Wrappers\DataObjectPool\DataObjectPoolInterface;
use Angie\Cache\CacheWrapper;
use Angie\Cache\CacheWrapperInterface;
use Angie\Globalization\WorkdayResolver;
use Angie\Globalization\WorkdayResolverInterface;
use Angie\Launcher\Launcher;
use Angie\Launcher\LauncherInterface;
use Angie\Memories\MemoriesWrapper;
use Angie\Memories\MemoriesWrapperInterface;
use Angie\Migrations\Migrations;
use Angie\Migrations\MigrationsInterface;
use Angie\Notifications\Notifications;
use Angie\Notifications\NotificationsInterface;
use Angie\Utils\ConfigReader\ConfigReader;
use Angie\Utils\ConfigReader\ConfigReaderInterface;
use Angie\Utils\CurrentTimestamp;
use Angie\Utils\SystemDateResolver\SystemDateResolver;
use Angie\Utils\SystemDateResolver\SystemDateResolverInterface;
use AngieApplication;
use DB;
use function DI\get;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    ApplicationModeInterface::class => function () {
        return new ApplicationMode(
            defined('APPLICATION_MODE')
                && in_array(APPLICATION_MODE, ApplicationModeInterface::MODES)
                ? APPLICATION_MODE
                : ApplicationModeInterface::IN_PRODUCTION,
            defined('ANGIE_IN_TEST') && ANGIE_IN_TEST
        );
    },

    RootUrlInterface::class => function () {
        return new RootUrl(ROOT_URL);
    },

    // @TODO Remove global dependency on AngieApplication.
    LoggerInterface::class => function () {
        return AngieApplication::log();
    },

    CacheWrapperInterface::class => get(CacheWrapper::class),
    ConfigReaderInterface::class => get(ConfigReader::class),
    CurrentTimestampInterface::class => get(CurrentTimestamp::class),
    LauncherInterface::class => get(Launcher::class),
    MigrationsInterface::class => get(Migrations::class),
    NotificationsInterface::class => get(Notifications::class),
    HtmlToDomConverterInterface::class => get(HtmlToDomConverter::class),
    DataObjectPoolInterface::class => get(DataObjectPool::class),
    ConfigOptionsInterface::class => get(ConfigOptions::class),
    TextReplacementResolverInterface::class => get(TextReplacementResolver::class),
    IgnoredDomainsResolverInterface::class => function () {
        $ignored_domains = (string) getenv('ACTIVECOLLAB_IGNORED_DOMAINS');

        if (empty($ignored_domains)) {
            $ignored_domains = [];
        } else {
            $ignored_domains = explode(',', $ignored_domains);
        }

        return new IgnoredDomainsResolver(...$ignored_domains);
    },

    // @TODO Remove global dependency on AngieApplication.
    RouteMapperFactoryInterface::class => function () {
        return new RouteMapperFactory(
            ANGIE_PATH . '/frameworks',
            AngieApplication::getFrameworkNames(),
            APPLICATION_PATH . '/modules',
            AngieApplication::getModuleNames()
        );
    },
    RouteMapperInterface::class => function (ContainerInterface $c) {
        return $c->get(RouteMapperFactoryInterface::class)->createMapper();
    },
    UrlAssemblerInterface::class => function (ContainerInterface $c) {
        if ($c->get(ApplicationModeInterface::class)->isInDevelopment()) {
            return $c->get(LiveUrlAssembler::class);
        } else {
            return $c->get(CompiledUrlAssembler::class);
        }
    },
    UrlMatcherInterface::class => function (ContainerInterface $c) {
        if ($c->get(ApplicationModeInterface::class)->isInDevelopment()) {
            return $c->get(LiveUrlMatcher::class);
        } else {
            return $c->get(CompiledUrlMatcher::class);
        }
    },
    RouterInterface::class => get(Router::class),

    // @TODO Remove global dependency on DB class.
    MemoriesWrapperInterface::class => function () {
        return new MemoriesWrapper(DB::getConnection()->getLink());
    },

    SystemDateResolverInterface::class => get(SystemDateResolver::class),
    WorkdayResolverInterface::class => function (ContainerInterface $container) {
        $workdays = $container->get(ConfigReaderInterface::class)->getValue('time_workdays');

        if (empty($workdays) || !is_array($workdays)) {
            $workdays = [];
        }

        $workdays = array_map('intval', $workdays);

        return new WorkdayResolver($workdays);
    },
];
