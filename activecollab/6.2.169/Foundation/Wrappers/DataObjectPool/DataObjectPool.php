<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Wrappers\DataObjectPool;

use DataObject;
use DataObjectPool as WrappedDataObjectPool;

class DataObjectPool implements DataObjectPoolInterface
{
    public function get(string $type, ?int $id): ?DataObject
    {
        return WrappedDataObjectPool::get($type, $id, null, false);
    }
}
