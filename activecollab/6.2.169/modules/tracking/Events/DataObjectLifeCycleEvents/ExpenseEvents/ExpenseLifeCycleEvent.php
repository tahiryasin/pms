<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\ExpenseEvents;

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEvent;
use Expense;

abstract class ExpenseLifeCycleEvent extends DataObjectLifeCycleEvent implements ExpenseLifeCycleEventInterface
{
    public function __construct(Expense $object)
    {
        parent::__construct($object);
    }
}
