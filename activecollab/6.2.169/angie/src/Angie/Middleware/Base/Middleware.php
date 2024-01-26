<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Middleware\Base;

use Angie\Middleware\MiddlewareInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Angie\Middleware
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface|null
     */
    protected function getLogger()
    {
        return $this->logger;
    }
}
