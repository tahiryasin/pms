<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\UserEvents;

use ActiveCollab\Foundation\Events\WebhookEvent\DataObjectLifeCycleWebhookEventTrait;
use User;

class UserCreatedEvent extends UserLifeCycleEvent implements UserCreatedEventInterface
{
    use DataObjectLifeCycleWebhookEventTrait;

    private $webhook_event_type;

    public function __construct(User $object)
    {
        parent::__construct($object);

        $this->webhook_event_type = get_class($this->getObject()) . 'Created';
    }

    public function getWebhookEventType(): string
    {
        return $this->webhook_event_type;
    }
}
