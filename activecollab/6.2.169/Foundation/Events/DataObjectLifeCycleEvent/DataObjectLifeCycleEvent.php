<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent;

use ActiveCollab\Foundation\Events\Event;
use DataObject;

abstract class DataObjectLifeCycleEvent extends Event implements DataObjectLifeCycleEventInterface
{
    private $object;

    public function __construct(DataObject $object)
    {
        $this->object = $object;
    }

    public function getObject(): DataObject
    {
        return $this->object;
    }
}
