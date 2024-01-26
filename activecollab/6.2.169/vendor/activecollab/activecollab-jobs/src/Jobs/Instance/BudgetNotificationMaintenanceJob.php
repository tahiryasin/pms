<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Instance;

use Exception;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class BudgetNotificationMaintenanceJob extends MaintenanceJob
{
    public function __construct(array $data = null)
    {
        $data['command'] = 'budget_notifications_maintenance';
        if (!isset($data['ondemand'])) {
            $data['ondemand'] = true;
        }

        parent::__construct($data);
    }

    public function execute()
    {
        $instance_id = $this->getInstanceId();
        $logger = $this->getLogger();
        try {
            if($this->getData('ondemand')){
                $this->runActiveCollabCliCommand(
                    $instance_id,
                    $this->getData('command'),
                    "Budget Notification Maintenance for account #{$instance_id} has started checking projects.",
                    $logger
                );
            } else {
                $php_finder = new PhpExecutableFinder();
                if($php_path = $php_finder->find()){
                    $process = new Process([
                        $php_path,
                        'tasks/activecollab-cli.php',
                        $this->getData('command')
                    ]);
                    $process->run();
                }
            }


            if ($logger) {
                $logger->info(
                    'Budget notification runner at account #{account_id} has finished his job.',
                    $this->getLogContextArguments(
                        [
                            'account_id' => $instance_id,
                        ]
                    )
                );
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
