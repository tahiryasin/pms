<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Storage;

use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use Angie\Storage\Adapter\StorageAdapterInterface;
use Angie\Storage\Capacity\CapacityCalculatorInterface;
use Angie\Storage\FilesIndex\FilesIndexBuilder;
use Angie\Storage\Usage\StorageUsageSnapshotFactory;
use Angie\Storage\Usage\StorageUsageSnapshotInterface;
use DateValue;
use InvalidArgumentException;

class Storage implements StorageInterface
{
    private $adapter;
    private $capacity_calculator;
    private $current_timestamp;

    public function __construct(
        StorageAdapterInterface $adapter,
        CapacityCalculatorInterface $capacity_calculator,
        CurrentTimestampInterface $current_timestamp
    )
    {
        $this->adapter = $adapter;
        $this->capacity_calculator = $capacity_calculator;
        $this->current_timestamp = $current_timestamp;
    }

    private $snapshots = [];

    public function getUsageSnapshot(DateValue $day = null, bool $reload = false): StorageUsageSnapshotInterface
    {
        if (empty($day)) {
            $day = DateValue::makeFromTimestamp($this->current_timestamp->getCurrentTimestamp());
        }

        $key = $day->format('Y-m-d');

        if (empty($this->snapshots[$key]) || $reload) {
            $this->snapshots[$key] = (new StorageUsageSnapshotFactory($this))->getSnapshotForDay($day);
        }

        return $this->snapshots[$key];
    }

    public function getServices(): array
    {
        return self::SERVICES;
    }

    public function getServiceName(string $service): string
    {
        switch ($service) {
            case self::SERVICE_LOCAL:
                return 'Local Files';
            case self::SERVICE_WAREHOUSE:
                return 'Warehouse';
            case self::SERVICE_GOOGLE_DRIVE:
                return 'Google Drive';
            case self::SERVICE_DROPBOX:
                return 'Dropbox';
            default:
                throw new InvalidArgumentException("Invalid storage service '{$service}'");
        }
    }

    public function getServiceTypeFromFileType(string $file_type): string
    {
        if (in_array($file_type, self::DROPBOX_FILE_TYPES)) {
            return self::SERVICE_DROPBOX;
        } elseif (in_array($file_type, self::GOOGLE_DRIVE_FILE_TYPES)) {
            return self::SERVICE_GOOGLE_DRIVE;
        } elseif (in_array($file_type, self::WAREHOUSE_FILE_TYPES)) {
            return self::SERVICE_WAREHOUSE;
        } elseif (in_array($file_type, self::LOCAL_FILE_TYPES)) {
            return self::SERVICE_LOCAL;
        } else {
            throw new InvalidArgumentException("Unknown file type '{$file_type}'.");
        }
    }

    public function getDiskUsage(): int
    {
        return $this->getUsageSnapshot()->getUsage();
    }

    public function isDiskFull(bool $be_graceful = false): bool
    {
        return $this->capacity_calculator->isCapacityReached($this->getDiskUsage(), $be_graceful);
    }

    public function listWarehouseFiles(bool $include_trashed = false): array
    {
        return (new FilesIndexBuilder())->getFilesIndex(self::WAREHOUSE_FILE_TYPES, $include_trashed);
    }

    public function deleteFileByLocationAndType(string $location, string $file_type): void
    {
        $this->adapter->deleteFile($location, $this->getServiceTypeFromFileType($file_type));
    }
}
