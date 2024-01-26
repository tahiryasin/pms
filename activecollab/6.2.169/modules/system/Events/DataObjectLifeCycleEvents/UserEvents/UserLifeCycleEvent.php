<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\UserEvents;

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEvent;
use User;

class UserLifeCycleEvent extends DataObjectLifeCycleEvent implements UserLifeCycleEventInterface
{
    public function __construct(User $object)
    {
        parent::__construct($object);
    }
}
