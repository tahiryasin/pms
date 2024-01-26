<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Tracking interface.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
interface ITracking
{
    /**
     * Return default billable status for this object type.
     *
     * @return int
     */
    public function getDefaultBillableStatus();

    // ---------------------------------------------------
    //  Time
    // ---------------------------------------------------

    /**
     * Log time and return time record.
     *
     * @param  float      $value
     * @param  IUser      $user
     * @param  JobType    $job_type
     * @param  DateValue  $date
     * @param  int        $billable_status
     * @param  IUser      $by
     * @return TimeRecord
     */
    public function trackTime($value, IUser $user, JobType $job_type, DateValue $date, $billable_status = TimeRecord::BILLABLE, IUser $by = null);

    /**
     * Returns time records attached to parent object.
     *
     * Optional filter is billable status (or array of statuses)
     *
     * @param  User     $user
     * @param  mixed    $billable_status
     * @return DBresult
     */
    public function getTimeRecords(User $user, $billable_status = null);

    // ---------------------------------------------------
    //  Expenses
    // ---------------------------------------------------

    /**
     * Log time and return time record.
     *
     * @param  float           $value
     * @param  IUser           $user
     * @param  ExpenseCategory $category
     * @param  DateValue       $date
     * @param  int             $billable_status
     * @param  IUser           $by
     * @return TimeRecord
     */
    public function trackExpense($value, IUser $user, ExpenseCategory $category, DateValue $date, $billable_status = Expense::BILLABLE, IUser $by = null);

    /**
     * Returns tracked expenses attached to the parent parent object.
     *
     * Optional filter is billable status (or array of statuses)
     *
     * @param  User     $user
     * @param  mixed    $billable_status
     * @return DBResult
     */
    public function getExpenses(User $user, $billable_status = null);

    /**
     * Return true if $user can track time for this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canTrackTime(User $user);

    /**
     * Return true if $user can track expenses for this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canTrackExpenses(User $user);

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return parent object ID.
     *
     * @return int
     */
    public function getId();
}
