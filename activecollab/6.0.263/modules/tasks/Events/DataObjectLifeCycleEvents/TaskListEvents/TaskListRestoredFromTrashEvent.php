<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskListEvents;

use ActiveCollab\Foundation\Events\WebhookEvent\DataObjectLifeCycleWebhookEventTrait;

class TaskListRestoredFromTrashEvent extends TaskListLifeCycleEvent implements TaskListRestoredFromTrashEventInterface
{
    use DataObjectLifeCycleWebhookEventTrait;

    public function getWebhookEventType(): string
    {
        return 'TaskListRestoredFromTrash';
    }
}
