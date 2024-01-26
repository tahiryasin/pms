<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\UserEvents;

use ActiveCollab\Foundation\Events\WebhookEvent\DataObjectLifeCycleWebhookEventTrait;

class UserUpdatedEvent extends UserLifeCycleEvent implements UserUpdatedEventInterface
{
    use DataObjectLifeCycleWebhookEventTrait;

    public function getWebhookEventType(): string
    {
        return 'UserUpdated';
    }
}
