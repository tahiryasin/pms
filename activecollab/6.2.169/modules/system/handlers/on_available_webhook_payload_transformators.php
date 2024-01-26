<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_available_webhook_payload_transformators event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_available_webhook_payload_transformators event.
 *
 * @param array $transformators
 */
function system_handle_on_available_webhook_payload_transformators(array &$transformators)
{
    $transformators[] = SlackWebhookPayloadTransformator::class;
    $transformators[] = ZapierWebhookPayloadTransformator::class;
}
