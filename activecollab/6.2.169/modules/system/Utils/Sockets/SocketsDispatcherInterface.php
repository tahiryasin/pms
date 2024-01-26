<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Utils\Sockets;

use DataObject;

interface SocketsDispatcherInterface
{
    public function dispatch(DataObject $object, string $event_type, bool $dispatch_partial_data = false);
}
