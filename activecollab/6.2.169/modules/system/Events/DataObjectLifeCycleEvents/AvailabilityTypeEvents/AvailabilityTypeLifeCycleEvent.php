<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\AvailabilityTypeEvents;

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEvent;
use AvailabilityType;

abstract class AvailabilityTypeLifeCycleEvent extends DataObjectLifeCycleEvent implements AvailabilityTypeLifeCycleEventInterface
{
    public function __construct(AvailabilityType $object)
    {
        parent::__construct($object);
    }
}
