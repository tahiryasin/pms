<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\ActiveCollabJobs\Jobs\Instance;


use Exception;

class CreateAccountStorageDirectories extends MaintenanceJob
{
    public function execute()
    {
        $logger = $this->getLogger();

        $instance_id = $this->getData('instance_id');

        try {
            $this->createAccountStorageDirectories($instance_id, $logger);

            if ($logger) {
                $logger->info(
                    'Upload and Thumbnails directories has been created.',
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
