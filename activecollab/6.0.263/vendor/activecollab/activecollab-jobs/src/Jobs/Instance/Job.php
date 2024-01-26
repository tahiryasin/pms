<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Instance;

use ActiveCollab\ActiveCollabJobs\Jobs\Job as BaseJob;
use InvalidArgumentException;

/**
 * @package ActiveCollab\ActiveCollabJobs\Jobs\Instance
 */
abstract class Job extends BaseJob
{
    const CLASSIC = 'classic';
    const FEATHER = 'feather';

    /**
     * @var string
     */
    private $instance_path = false;

    /**
     * @var string
     */
    private $multi_account_path = false;

    /**
     * Construct a new Job instance.
     *
     * @param  array|null               $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = null)
    {
        if (empty($data['instance_type'])) {
            throw new InvalidArgumentException("'instance_type' property is required");
        } elseif (!in_array($data['instance_type'], [self::CLASSIC, self::FEATHER])) {
            throw new InvalidArgumentException("'instance_type' can be 'classic' or 'feather'");
        }

        if (empty($data['tasks_path'])) {
            $data['tasks_path'] = '';
        }

        parent::__construct($data);
    }

    protected function getMultiAccountPath()
    {
        if ($this->multi_account_path === false) {
            $this->multi_account_path = '/var/www/activecollab-multi-account';
        }

        return $this->multi_account_path;
    }

    public function getActiveCollabCliPhpPath()
    {
        return "{$this->getMultiAccountPath()}/tasks/activecollab-cli.php";

    }

    /**
     * @return string
     */
    protected function getShepherdPath()
    {
        return '/var/www/shepherd';
    }
}
