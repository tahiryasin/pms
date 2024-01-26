<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level application objects implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
abstract class FwApplicationObjects
{
    /**
     * Returns an array where key is path name (users, projects/12 etc) and value is either:.
     *
     * - TRUE - all objects in that context
     * - INT[] - array of ID-s from a particular context that are visible
     *
     * @param  User                   $user
     * @param  ApplicationObject|null $in
     * @return array
     */
    public static function getVisibleObjectPaths(User $user, $in = null)
    {
        $paths = [];
        Angie\Events::trigger('on_visible_object_paths', [$user, &$paths, &$in]);

        return $paths;
    }
}
