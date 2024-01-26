<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level days off manager implemetaiton.
 *
 * @package angie.frameworks.globalization
 * @subpackage models
 */
class FwDayOffs extends BaseDayOffs
{
    /**
     * Returns true if $user can define a new day off.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isOwner();
    }
}
