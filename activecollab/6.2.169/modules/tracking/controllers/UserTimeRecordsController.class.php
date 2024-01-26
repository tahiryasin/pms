<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('users', SystemModule::NAME);

/**
 * User time records controller.
 *
 * @package activeCollab.modules.tracking
 * @subpackage controllers
 */
class UserTimeRecordsController extends UsersController
{
    /**
     * Show user assignments.
     *
     * @return UserTimeRecordsCollection|int
     */
    public function index(Request $request, User $user)
    {
        return TimeRecords::canAccessUsersTimeRecords($user, $this->active_user) ?
            TimeRecords::prepareCollection('time_records_by_user_' . $this->active_user->getId() . '_page_' . $request->getPage(), $user) :
            Response::NOT_FOUND;
    }

    /**
     * @return int|UserTimeRecordsCollection
     */
    public function filtered_by_date(Request $request, User $user)
    {
        if ($this->active_user->isLoaded() && ($user->is($this->active_user) || $user->isOwner())) {
            if ($this->active_user instanceof Client) {
                return Response::NOT_FOUND;
            }

            $from_string = $request->get('from');
            $to_string = $request->get('to');

            $from = $from_string ? DateValue::makeFromString($from_string) : null;
            $to = $to_string ? DateValue::makeFromString($to_string) : null;

            if ($from instanceof DateValue && $to instanceof DateValue) {
                if (AngieApplication::featureFlags()->isEnabled('user_timesheet')) {
                    return TimeRecords::prepareCollection('user_timesheet_report_for_' . $this->active_user->getId() . '_' . $from->toMySQL() . ':' . $to->toMySQL(), $user);
                } else {
                    return TimeRecords::prepareCollection('filtered_time_records_by_user_' . $this->active_user->getId() . '_' . $from->toMySQL() . ':' . $to->toMySQL(), $user);
                }
            }

            return Response::BAD_REQUEST;
        }

        return Response::NOT_FOUND;
    }
}
