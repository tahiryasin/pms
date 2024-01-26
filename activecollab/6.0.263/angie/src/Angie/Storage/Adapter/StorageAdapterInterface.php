<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Storage\Adapter;

interface StorageAdapterInterface
{
    /**
     * Delete file by location.
     *
     * @param string|null $location
     * @param string      $serivce_type
     */
    public function deleteFile(?string $location, string $serivce_type);
}
