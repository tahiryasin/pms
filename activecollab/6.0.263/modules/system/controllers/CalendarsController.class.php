<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;

AngieApplication::useController('fw_calendars', CalendarsFramework::NAME);

/**
 * Calendars controller.
 *
 * @package ActiveCollab.modules.system
 * @subpackage controllers
 */
class CalendarsController extends FwCalendarsController
{
    /**
     * Return all calendar events for this user.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function all_calendar_events(Request $request, User $user)
    {
        return CalendarEvents::prepareCollection('all_events_for_period_' . $request->get('from') . '_' . $request->get('to'), $user);
    }
}
