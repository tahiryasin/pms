<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Webhook payload transformator interface definition.
 *
 * @package angie.framework.environment
 * @subpackage models
 */
interface WebhookPayloadTransformatorInterface
{
    /**
     * Return true if the payload of a webhook should be transformed.
     *
     * @param  string $url
     * @return bool
     */
    public function shouldTransform($url);

    /**
     * Transform the webhook payload.
     *
     * @param  string     $event_type
     * @param  DataObject $payload
     * @return array|null
     */
    public function transform($event_type, DataObject $payload);

    /**
     * Return an array of supported events.
     *
     * @return array
     */
    public function getSupportedEvents();
}
