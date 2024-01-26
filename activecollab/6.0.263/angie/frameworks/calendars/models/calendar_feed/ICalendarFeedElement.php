<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Calendar feed element interface.
 */
interface ICalendarFeedElement
{
    /**
     * Skip feed if need.
     *
     * @return bool
     */
    public function skipCalendarFeed();

    /**
     * Return event unique id.
     *
     * @return string
     */
    public function getCalendarFeedUID();

    public function getCalendarFeedSummary(IUser $user, $prefix = '', $sufix = ''): string;

    /**
     * Return event description.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getCalendarFeedDescription(IUser $user);

    /**
     * Return event start date.
     *
     * @return DateValue|DateTimeValue|null
     */
    public function getCalendarFeedDateStart();

    /**
     * Return event end date.
     *
     * @return DateValue|DateTimeValue|null
     */
    public function getCalendarFeedDateEnd();

    /**
     * Return event repeating rule.
     *
     * @return string|null
     */
    public function getCalendarFeedRepeatingRule();
}
