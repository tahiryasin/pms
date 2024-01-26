<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Wrappers\DataObjectPool;

use DataObject;

interface DataObjectPoolInterface
{
    public function get(string $type, ?int $id): ?DataObject;
}
