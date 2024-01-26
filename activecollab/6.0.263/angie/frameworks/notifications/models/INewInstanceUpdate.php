<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Trait that records that new instance was created (used by user object updates collection).
 *
 * @package angie.frameworks.notifications
 * @subpackage models
 */
trait INewInstanceUpdate
{
    /**
     * Set update flags for combined object updates collection.
     *
     * @param array $updates
     */
    public function onObjectUpdateFlags(array &$updates)
    {
        $updates['new_instance'] = 1;
    }
}
