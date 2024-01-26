<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Model\FeaturePointer;

use FeaturePointer;
use User;

class TimesheetFeaturePointer extends FeaturePointer
{
    public function shouldShow(User $user): bool
    {
        return $user->isPowerUser() &&
            $this->getCreatedOn()->getTimestamp() > $user->getCreatedOn()->getTimestamp() &&
            parent::shouldShow($user);
    }

    public function getDescription(): string
    {
        return lang('The Company Timesheet is now available!');
    }
}
