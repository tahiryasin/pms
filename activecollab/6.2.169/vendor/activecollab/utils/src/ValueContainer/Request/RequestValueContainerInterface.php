<?php

/*
 * This file is part of the Active Collab Utils project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ValueContainer\Request;

use ActiveCollab\ValueContainer\ValueContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package ActiveCollab\ValueContainer\Request
 */
interface RequestValueContainerInterface extends ValueContainerInterface
{
    /**
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * @param  ServerRequestInterface $request
     * @return $this
     */
    public function &setRequest(ServerRequestInterface $request);
}
