<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Basic calendar element feed implementation.
 *
 * @package angie.frameworks.calendars
 * @subpackage models
 */
trait ICalendarFeedElementImplementation
{
    /**
     * {@inheritdoc}
     */
    public function skipCalendarFeed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedUID()
    {
        $id = $this->getId();
        $type = $this->getVerboseType();
        $prefix = 'ac';

        if ($this instanceof CalendarEvent) {
            $prefix .= "_calendar_{$this->getCalendar()->getId()}";
        } elseif ($this instanceof IProjectElement) {
            $prefix .= "_project_{$this->getProject()->getId()}";
        }

        $timestamp = $this->getCreatedOn()->getTimestamp();

        return md5("{$prefix}_{$type}_{$id}_{$timestamp}");
    }

    public function getCalendarFeedSummary(IUser $user, $prefix = '', $sufix = ''): string
    {
        return $prefix . $this->getName() . $sufix;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedDescription(IUser $user)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedDateStart()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedDateEnd()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedRepeatingRule()
    {
        return null;
    }
}
