<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('selected_object', EnvironmentFramework::INJECT_INTO);

/**
 * Framework level access logs controller.
 *
 * @package angie.frameworks.environment
 * @subpackage controllers
 */
abstract class FwAccessLogsController extends SelectedObjectController
{
    /**
     * Selected object.
     *
     * @var ApplicationObject|IAccessLog
     */
    protected $active_object;

    /**
     * Instance of check after object gets loaded.
     *
     * @var string
     */
    protected $active_object_instance_of = IAccessLog::class;

    /**
     * List access logs.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return array|int
     */
    public function index(Request $request, User $user)
    {
        return $this->active_object->canViewAccessLogs($user)
            ? AccessLogs::findByParent($this->active_object)
            : Response::NOT_FOUND;
    }

    /**
     * @param  Request        $request
     * @param  User           $user
     * @return int|IAccessLog
     */
    public function log_access(Request $request, User $user)
    {
        return $this->active_object->canView($user)
            ? AccessLogs::logAccess($this->active_object, $user)
            : Response::NOT_FOUND;
    }
}
