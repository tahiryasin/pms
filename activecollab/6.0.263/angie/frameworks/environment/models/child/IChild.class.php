<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Basic child interface.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
interface IChild
{
    /**
     * Return parent type.
     *
     * @return string
     */
    public function getParentType();

    /**
     * Return parent ID.
     *
     * @return int
     */
    public function getParentId();

    /**
     * Return parent instance.
     *
     * @return ApplicationObject|null
     */
    public function &getParent();

    /**
     * Set parent instance.
     *
     * @param  ApplicationObject|null $parent
     * @param  bool                   $save
     * @return ApplicationObject
     * @throws InvalidInstanceError
     */
    public function setParent($parent, $save = false);

    /**
     * Return true if $check is parent of this child.
     *
     * @param  ApplicationObject $parent
     * @return bool
     */
    public function isParent(ApplicationObject $parent);

    /**
     * Return true if parent is optional.
     *
     * @return bool
     */
    public function isParentOptional();

    /**
     * Set prevent touch on next delete.
     */
    public function preventTouchOnNextDelete();
}
