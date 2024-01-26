<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reminders interface definition.
 *
 * @package angie.frameworks.reminders
 * @subpackage models
 */
interface IReminders
{
    /**
     * Return reminders.
     *
     * @return Reminder[]|null
     */
    public function getReminders();

    /**
     * @return int
     */
    public function getId();

    /**
     * Return true if $user can view object that implements this.
     *
     * @param  User  $user
     * @return mixed
     */
    public function canView(User $user);
}
