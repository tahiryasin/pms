<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs;

use ActiveCollab\ContainerAccess\ContainerAccessInterface;
use ActiveCollab\ContainerAccess\ContainerAccessInterface\Implementation as ContainerAccessInterfaceImplementation;
use ActiveCollab\JobsQueue\Jobs\Job as BaseJob;
use InvalidArgumentException;

/**
 * @property \ActiveCollab\DatabaseConnection\ConnectionInterface $connection
 * @property \ActiveCollab\DatabaseConnection\ConnectionInterface $shepherd_account_connection
 * @property \ActiveCollab\JobsQueue\DispatcherInterface $dispatcher
 * @property \ActiveCollab\Logger\LoggerInterface $log
 */
abstract class Job extends BaseJob implements ContainerAccessInterface
{
    use ContainerAccessInterfaceImplementation;

    /**
     * Construct a new Job instance.
     *
     * @param  array|null               $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = null)
    {
        if (empty($data['instance_id'])) {
            throw new InvalidArgumentException("'instance_id' property is required");
        } else {
            if (!is_int($data['instance_id'])) {
                if (is_string($data['instance_id']) && ctype_digit($data['instance_id'])) {
                    $data['instance_id'] = (int) $data['instance_id'];
                } else {
                    throw new InvalidArgumentException(
                        "Value '$data[instance_id]' is not a valid instance ID'"
                    );
                }
            }
        }

        parent::__construct($data);
    }

    protected function getInstanceId(): int
    {
        if ($instance_id = $this->getData('instance_id')) {
            if (!is_int($instance_id) && ctype_digit($instance_id)) {
                $instance_id = (integer) $instance_id;
            }

            if ($instance_id > 0) {
                return $instance_id;
            } else {
                throw new InvalidArgumentException("Value '$instance_id' is not a valid instance ID");
            }
        } else {
            throw new InvalidArgumentException('Instance ID not set');
        }
    }
}
