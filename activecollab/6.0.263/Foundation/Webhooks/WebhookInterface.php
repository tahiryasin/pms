<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Webhooks;

use ActiveCollab\Foundation\Events\WebhookEvent\WebhookEventInterface;

interface WebhookInterface
{
    public function filterEvent(WebhookEventInterface $webhook_event): bool;
    public function getCustomQueryParams(WebhookEventInterface $webhook_event = null): string;
    public function getCustomHeaders(WebhookEventInterface $webhook_event = null): array;
}
