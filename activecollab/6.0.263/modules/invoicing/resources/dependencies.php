<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Foundation\Wrappers\ConfigOptions\ConfigOptionsInterface;
use ActiveCollab\Foundation\Wrappers\DataObjectPool\DataObjectPoolInterface;
use ActiveCollab\Module\Invoicing\Utils\VariableProcessor\Factory\VariableProcessorFactory;
use ActiveCollab\Module\Invoicing\Utils\VariableProcessor\Factory\VariableProcessorFactoryInterface;
use Psr\Container\ContainerInterface;

return [
    VariableProcessorFactoryInterface::class => function (ContainerInterface $c) {
        return new VariableProcessorFactory(
            $c->get(DataObjectPoolInterface::class),
            $c->get(ConfigOptionsInterface::class),
            FORMAT_DATE
        );
    },
];
