<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Storage;

use Angie\Storage\Usage\StorageUsageSnapshotInterface;
use DateValue;
use DropboxAttachment;
use DropboxFile;
use DropboxUploadedFile;
use GoogleDriveAttachment;
use GoogleDriveFile;
use GoogleDriveUploadedFile;
use LocalAttachment;
use LocalFile;
use LocalUploadedFile;
use WarehouseAttachment;
use WarehouseFile;
use WarehouseUploadedFile;

interface StorageInterface
{
    const SERVICE_LOCAL = 'local';
    const SERVICE_WAREHOUSE = 'warehouse';
    const SERVICE_GOOGLE_DRIVE = 'google_drive';
    const SERVICE_DROPBOX = 'dropbox';

    const SERVICES = [
        self::SERVICE_LOCAL,
        self::SERVICE_WAREHOUSE,
        self::SERVICE_GOOGLE_DRIVE,
        self::SERVICE_DROPBOX,
    ];

    const LOCAL_FILE_TYPES = [
        LocalFile::class,
        LocalAttachment::class,
        LocalUploadedFile::class,
    ];

    const WAREHOUSE_FILE_TYPES = [
        WarehouseFile::class,
        WarehouseAttachment::class,
        WarehouseUploadedFile::class,
    ];

    const GOOGLE_DRIVE_FILE_TYPES = [
        GoogleDriveFile::class,
        GoogleDriveAttachment::class,
        GoogleDriveUploadedFile::class,
    ];

    const DROPBOX_FILE_TYPES = [
        DropboxFile::class,
        DropboxAttachment::class,
        DropboxUploadedFile::class,
    ];

    public function getUsageSnapshot(DateValue $day = null, bool $reload = false): StorageUsageSnapshotInterface;
    public function getServices(): array;
    public function getServiceName(string $service): string;
    public function getServiceTypeFromFileType(string $file_type): string;
    public function getDiskUsage(): int;
    public function isDiskFull(bool $be_graceful = false): bool;
    public function listWarehouseFiles(bool $include_trashed = false): array;
    public function deleteFileByLocationAndType(string $location, string $file_type): void;
}
