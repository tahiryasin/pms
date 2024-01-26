<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Application level categories manager class.
 *
 * @package activeCollab.modules.system
 * @subpackage models
 */
class Categories extends FwCategories
{
    /**
     * Return new collection.
     *
     * Valid collections:
     *
     * - project_categories
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if ($collection_name === 'project_categories') {
            $collection->setConditions('type = ?', 'ProjectCategory');
        } else {
            throw new InvalidParamError('collection_name', $collection_name);
        }

        return $collection;
    }

    /**
     * Return true if $user can manage categories of a given type.
     *
     * @param  User                   $user
     * @param  string                 $type
     * @param  ApplicationObject|null $parent
     * @return bool
     */
    public static function canManage(User $user, $type, $parent = null)
    {
        if ($user->isPowerUser()) {
            return true;
        } else {
            return $parent instanceof Project ? $parent->isLeader($user) : false;
        }
    }
}
