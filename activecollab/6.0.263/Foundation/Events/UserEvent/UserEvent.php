<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Events\UserEvent;

use JsonSerializable;

abstract class UserEvent implements UserEventInterface, JsonSerializable
{
    public function getPayloadVersion(): string
    {
        return '1.0';
    }

    public function jsonSerialize()
    {
        return [
            'version' => $this->getPayloadVersion(),
        ];
    }
}
