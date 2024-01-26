<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Utils\Sockets;

use ActiveCollab\JobsQueue\Batches\BatchInterface;
use ActiveCollab\JobsQueue\DispatcherInterface;
use ActiveCollab\Logger\LoggerInterface;
use ActiveCollab\Module\System\Utils\RealTimeIntegrationResolver\RealTimeIntegrationResolverInterface;
use DataObject;
use RealTimeIntegrationInterface;

class SocketsDispatcher implements SocketsDispatcherInterface
{
    private $resolver;
    private $jobs;
    private $logger;

    public function __construct(
        RealTimeIntegrationResolverInterface $resolver,
        DispatcherInterface $jobs,
        LoggerInterface $logger
    )
    {
        $this->resolver = $resolver;
        $this->jobs = $jobs;
        $this->logger = $logger;
    }

    public function dispatch(DataObject $object, string $event_type, bool $dispatch_partial_data = false)
    {
        $integration = $this->resolver->getIntegration();

        if ($integration instanceof RealTimeIntegrationInterface && $integration->isInUse()) {
            $socket = $integration->getSocket();

            if ($socket) {
                $requests = $socket->getRequests($event_type, $object, $dispatch_partial_data);

                if ($requests) {
                    if (count($requests) === 1) {
                        $this->jobs->dispatch($requests[0], RealTimeIntegrationInterface::JOBS_QUEUE_CHANNEL);
                    } else {
                        $this->jobs->batch(
                            $event_type,
                            function (BatchInterface $batch) use ($requests) {
                                foreach ($requests as $request) {
                                    $batch->dispatch($request, RealTimeIntegrationInterface::JOBS_QUEUE_CHANNEL);
                                }
                            }
                        );
                    }
                } else {
                    $this->logger->debug(
                        'Skipping event type {event_type}, no requests defined.',
                        ['event_type' => $event_type]
                    );
                }
            }
        }
    }
}
