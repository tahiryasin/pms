<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Reports;

use User;

/**
 * @package Angie\Reports
 */
interface Report
{
    const DONT_GROUP = 'dont';

    /**
     * @param  User       $user
     * @param  array|null $additional
     * @return array
     */
    public function run(User $user, $additional = null);

    /**
     * @param  User       $user
     * @param  array|null $additional
     * @return string
     */
    public function export(User $user, $additional = null);

    /**
     * Return true if $user can run this report.
     *
     * @param  User $user
     * @return bool
     */
    public function canRun(User $user);

    /**
     * Set object attributes / properties. This function will take hash and set
     * value of all fields that she finds in the hash.
     *
     * @param array $attributes
     */
    public function setAttributes($attributes);

    /**
     * Return an array of columns that can be used to group the result.
     *
     * @return array|false
     */
    public function canBeGroupedBy();

    /**
     * Return max level of result grouping.
     *
     * @return int
     */
    public function getGroupingMaxLevel();

    /**
     * Return true if result of this report is grouped.
     *
     * @return bool
     */
    public function isGrouped();

    /**
     * Return array of properties that this report should be grouped by.
     *
     * @return array
     */
    public function getGroupBy();

    /**
     * Set group by.
     */
    public function setGroupBy();

    /**
     * Reset group by settings.
     */
    public function ungroup();
}
