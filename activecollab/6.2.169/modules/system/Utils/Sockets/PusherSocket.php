<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Utils\Sockets;

use ActiveCollab\ActiveCollabJobs\Jobs\Http\SendRequest;
use ActiveCollab\JobsQueue\Jobs\Job;
use AngieApplication;
use DataObject;
use PusherSocketPayloadPartialTransformator;
use PusherSocketPayloadTransformator;
use RealTimeIntegrationInterface;
use RealTimeUsersChannelsResolver;
use SocketPayloadTransformatorInterface;

/**
 * Pusher socket class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class PusherSocket extends Socket
{
    const CHANNELS_PER_REQUEST = 100;

    public function getRequests(
        string $event_type,
        DataObject $object,
        bool $requests_with_partial_data = false
    ): array
    {
        $requests = [];

        /** @var \RealTimeIntegration $real_time_integration */
        $real_time_integration = AngieApplication::realTimeIntegrationResolver()->getIntegration();
        $user_channel_resolver = new RealTimeUsersChannelsResolver();

        $this->makeRequests(
            $requests,
            $event_type,
            $object,
            $real_time_integration,
            new PusherSocketPayloadTransformator(),
            $user_channel_resolver->getUsersChannels($object)
        );

        if ($requests_with_partial_data) {
            $this->makeRequests(
                $requests,
                $event_type,
                $object,
                $real_time_integration,
                new PusherSocketPayloadPartialTransformator(),
                $user_channel_resolver->getUsersChannels($object, true)
            );
        }

        return $requests;
    }

    private function makeRequests(
        array &$requests,
        string $event_type,
        DataObject $object,
        RealTimeIntegrationInterface $real_time_integration,
        SocketPayloadTransformatorInterface $payload_transformator,
        array $channels
    ): void
    {
        $chunks = ceil(count($channels) / self::CHANNELS_PER_REQUEST);
        $payload = [
            'name' => $event_type,
            'data' => json_encode($payload_transformator->transform($event_type, $object)),
        ];

        for ($i = 0; $i < $chunks; $i++) {
            $payload['channels'] = array_slice(
                $channels,
                $i * self::CHANNELS_PER_REQUEST,
                self::CHANNELS_PER_REQUEST
            );

            $url = $real_time_integration->getApiUrl() . $real_time_integration->getEventsPath() . '?';
            $url .= $real_time_integration->buildAuthQueryString('POST', $payload);

            $requests[] = new SendRequest(
                [
                    'priority' => Job::HAS_HIGHEST_PRIORITY,
                    'instance_id' => AngieApplication::getAccountId(),
                    'url' => $url,
                    'method' => 'POST',
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'payload' => json_encode($payload),
                ]
            );
        }
    }
}
