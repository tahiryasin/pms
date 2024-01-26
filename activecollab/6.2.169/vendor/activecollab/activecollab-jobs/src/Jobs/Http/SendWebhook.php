<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Http;

use ActiveCollab\ActiveCollabJobs\Jobs\Job;
use GuzzleHttp\Client;
use InvalidArgumentException;

class SendWebhook extends Job
{
    const DEFAULT_METHOD = 'POST';
    const DEFAULT_VERIFY = true;
    const DEFAULT_TIMEOUT = 30;

    public function __construct(array $data)
    {
        if (empty($data['event_type'])) {
            throw new InvalidArgumentException('Event type is required');
        }

        if (empty($data['return_url']) || empty($data['return_secret'])) {
            $data['return_url'] = '';
            $data['return_secret'] = '';
        }

        if ($data['return_url'] && !filter_var($data['return_url'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Return URL is not a valid URL');
        }

        if (!isset($data['verify']) || !is_bool($data['verify'])) {
            $data['verify'] = self::DEFAULT_VERIFY;
        }

        if (empty($data['url']) || !filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Valid URL is required');
        }

        if (empty($data['payload'])) {
            throw new InvalidArgumentException('Payload is required');
        }

        if (empty($data['headers'])) {
            $data['headers'] = [];
        }

        if (!array_key_exists('timeout', $data)) {
            $data['timeout'] = self::DEFAULT_TIMEOUT;
        }

        parent::__construct($data);
    }

    public function execute()
    {
        $client = new Client();

        $headers = $this->getData('headers');
        $headers['User-Agent'] = 'Active Collab';

        $request_options = [
            'headers' => $headers,
            'timeout' => $this->getData('timeout'),
            'verify' => (bool) $this->getData('verify'),
        ];

        $request_options['body'] = $this->getData('payload');

        $request = $client->createRequest(
            'POST',
            $this->getData('url'),
            $request_options
        );

        $client->send($request);
    }
}
