<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;

AngieApplication::useController('auth_required', SystemModule::NAME);

class WhatsNewController extends AuthRequiredController
{
    public function index(Request $request, User $user)
    {
        return Users::prepareCollection(
            'activity_logs_for_' . $user->getId() . '_page_' . $request->getPage(),
            $user
        );
    }

    public function daily(Request $request, User $user)
    {
        return Users::prepareCollection(
            'daily_activity_logs_for_' . $user->getId() . '_' . $request->get('day') . '_page_' . $request->getPage(),
            $user
        );
    }
}
