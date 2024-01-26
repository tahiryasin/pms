<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Instance;

use InvalidArgumentException;


/**
 * @package ActiveCollab\ActiveCollabJobs\Jobs\Instance
 */
class UserAnonymizeJob extends ExecuteActiveCollabCliCommand
{
    public function __construct(array $data = null)
    {
        if (empty($data['email'])) {
            throw new InvalidArgumentException("'Email' property is required");
        }

        parent::__construct(
            array_merge(
                $data,
                [
                    'command' => 'ondemand:user:anonymize',
                    'command_arguments' => [
                        $data['email']
                    ],
                    'command_options' => [],
                ]
            )
        );
    }
}
