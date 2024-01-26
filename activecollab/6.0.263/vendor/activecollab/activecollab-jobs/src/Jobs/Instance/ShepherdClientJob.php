<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Instance;


use ActiveCollab\JobsQueue\Jobs\Job;
use ActiveCollab\ShepherdSDK\Client;
use ActiveCollab\ShepherdSDK\Token;
use ActiveCollab\ShepherdSDK\Utils\UrlCreator\UrlCreator;
use InvalidArgumentException;

abstract class ShepherdClientJob extends Job
{
    public function __construct(array $data = null)
    {
        if (empty($data['access_token'])) {
            throw new InvalidArgumentException('Shepherd Access Token is required');
        }

        if (empty($data['shepherd_url'])) {
            throw new InvalidArgumentException('Shepherd URL is required');
        }

        if (empty($data['headers'])) {
            $data['headers'] = [];
        }

        if (!array_key_exists('timeout', $data)) {
            $data['timeout'] = 30;
        }

        parent::__construct($data);
    }

    protected function getShepherdClient(array $data)
    {
        return new Client(
            new Token($data['access_token']),
            $data['shepherd_url']
        );
    }

    protected function getUrlCreator(array $data)
    {
        return new UrlCreator($data['shepherd_url']);
    }
}
