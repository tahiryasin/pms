<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Complete interface definition.
 *
 * @package angie.frameworks.complete
 * @subpackage models
 */
interface IComplete
{
    /**
     * Mark this object as completed.
     *
     * @param User $by
     * @param bool $bulk
     */
    public function complete(User $by, $bulk = false);

    /**
     * Mark this item as opened.
     *
     * @param User $by
     * @param bool $bulk
     */
    public function open(User $by, $bulk = false);

    /**
     * Returns true if this object is marked as completed.
     *
     * @return bool
     */
    public function isCompleted();

    /**
     * Returns true if this object is open (not completed).
     *
     * @return bool
     */
    public function isOpen();

    /**
     * Return value of completed_on field.
     *
     * @return DateTimeValue
     */
    public function getCompletedOn();

    /**
     * Return user who completed this object.
     *
     * @return IUser|null
     */
    public function getCompletedBy();

    /**
     * Return value of completed_by_id field.
     *
     * @return int
     */
    public function getCompletedById();

    /**
     * Return true if $user can change completion status.
     *
     * @param  User $user
     * @return bool
     */
    public function canChangeCompletionStatus(User $user);
}
