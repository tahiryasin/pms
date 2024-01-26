<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\System\Model\FeaturePointer;

use AngieApplication;
use FeaturePointer;
use User;

class NewColumnViewFeaturePointer extends FeaturePointer
{
    public function shouldShow(User $user): bool
    {
        return AngieApplication::featureFlags()->isEnabled('react_column_view') && parent::shouldShow($user);
    }

    public function getDescription(): string
    {
        return lang('Check out the brand new Column View! Now with a column dedicated to completed tasks.');
    }
}
