<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Storage\Adapter;

use ActiveCollab\Logger\LoggerInterface;
use Angie\Storage\StorageInterface;
use AngieApplication;

abstract class StorageAdapter implements StorageAdapterInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function deleteFile(?string $location, string $service_type)
    {
        if ($service_type === StorageInterface::SERVICE_LOCAL) {
            $file_path = AngieApplication::fileLocationToPath($location);

            if (is_file($file_path)) {
                @unlink($file_path);
            } else {
                $this->logger->warning(
                    'Attempted to delete non existing local file with location: {location}',
                    [
                        'location' => $location,
                    ]
                );
            }
        }
    }
}
