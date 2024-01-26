<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Utils\Sockets;

use DataObject;

interface SocketInterface
{
    public function getRequests(string $event_type, DataObject $object): array;
}
