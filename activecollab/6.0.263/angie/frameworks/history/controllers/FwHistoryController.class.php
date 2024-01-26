<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;

AngieApplication::useController('selected_object', EnvironmentFramework::INJECT_INTO);

/**
 * Class ModificationLogsController.
 */
class FwHistoryController extends SelectedObjectController
{
    /**
     * Selected object.
     *
     * @var DataObject|IHistory
     */
    protected $active_object;

    /**
     * Instance of check after object gets loaded.
     *
     * @var string
     */
    protected $active_object_instance_of = 'IHistory';

    /**
     * List access logs.
     *
     * @param  Request $request
     * @param  User    $user
     * @return array
     */
    public function index(Request $request, User $user)
    {
        if ($request->get('verbose')) {
            return $this->active_object->getVerboseHistory($user->getLanguage());
        }

        return $this->active_object->getHistory();
    }
}
