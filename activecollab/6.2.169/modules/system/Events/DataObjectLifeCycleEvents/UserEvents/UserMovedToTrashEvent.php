<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\UserEvents;

class UserMovedToTrashEvent extends UserUpdatedEvent implements UserMovedToTrashEventInterface
{
    public function getWebhookEventType(): string
    {
        return 'UserMovedToTrash';
    }
}
