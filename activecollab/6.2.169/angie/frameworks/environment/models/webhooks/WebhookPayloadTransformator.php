<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

abstract class WebhookPayloadTransformator implements WebhookPayloadTransformatorInterface
{
    public function shouldTransform($url)
    {
        return false;
    }

    public function transform($event_type, DataObject $payload)
    {
        return $payload;
    }

    public function getSupportedEvents()
    {
        return [];
    }
}
