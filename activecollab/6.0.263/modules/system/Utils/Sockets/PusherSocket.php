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
use Exception;
use PusherSocketPayloadTransformator;
use RealTimeUsersChannelsResolver;

/**
 * Pusher socket class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class PusherSocket extends Socket
{
    const CHANNELS_PER_REQUEST = 100;

    public function getRequests(string $event_type, DataObject $object): array
    {
        $requests = [];

        /** @var \RealTimeIntegration $real_time_integration */
        $real_time_integration = AngieApplication::realTimeIntegrationResolver()->getIntegration();
        $channels = (new RealTimeUsersChannelsResolver())->getUsersChannels($object);

        if ($real_time_integration->isInUse() && $channels) {
            $transformator = new PusherSocketPayloadTransformator();
            $chunks = ceil(count($channels) / self::CHANNELS_PER_REQUEST);
            $payload = [
                'name' => $event_type,
                'data' => json_encode($transformator->transform($event_type, $object)),
            ];

            for ($i = 0; $i < $chunks; $i++) {
                $payload['channels'] = array_slice(
                    $channels,
                    $i * self::CHANNELS_PER_REQUEST,
                    self::CHANNELS_PER_REQUEST
                );

                $url = $real_time_integration->getApiUrl() . $real_time_integration->getEventsPath() . '?';
                $url .= $real_time_integration->buildAuthQueryString('POST', $payload);

                try {
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
                } catch (Exception $e) {
                    // log error only, do not throw exception
                    AngieApplication::log()->error(
                        "SendRequest job for send real time object is failed with reason: '{reason}'.",
                        [
                            'reason' => $e->getMessage(),
                            'url' => $url,
                            'object_id' => $object->getId(),
                            'object_class' => get_class($object),
                            'event_type' => $event_type,
                            'payload' => $payload,
                        ]
                    );
                }
            }
        }

        return $requests;
    }
}
