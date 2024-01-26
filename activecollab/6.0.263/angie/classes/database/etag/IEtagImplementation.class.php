<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reusable etag code.
 *
 * @package angie.frameworks.environment
 * @subpackage
 */
trait IEtagImplementation
{
    /**
     * Check if provided etag value match the current record.
     *
     * @param  string $value
     * @param  IUser  $user
     * @param  bool   $use_cache
     * @return bool
     */
    public function validateTag($value, IUser $user, $use_cache = true)
    {
        return $this->getTag($user, $use_cache) === $value;
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return collection etag.
     *
     * @param  IUser  $user
     * @param  bool   $use_cache
     * @return string
     */
    abstract public function getTag(IUser $user, $use_cache = true);
}
