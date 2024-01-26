<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\ProjectEvents;

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEvent;
use Project;

abstract class ProjectLifeCycleEvent extends DataObjectLifeCycleEvent implements ProjectLifeCycleEventInterface
{
    public function __construct(Project $object)
    {
        parent::__construct($object);
    }
}