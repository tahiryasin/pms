<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\TimeRecordEvents;

use ActiveCollab\Foundation\Events\WebhookEvent\DataObjectLifeCycleWebhookEventTrait;

class TimeRecordRestoredFromTrashEvent extends TimeRecordLifeCycleEvent implements TimeRecordRestoredFromTrashEventInterface
{
    use DataObjectLifeCycleWebhookEventTrait;

    public function getWebhookEventType(): string
    {
        return 'TimeRecordRestoredFromTrash';
    }
}
