<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('teams', SystemModule::NAME);

/**
 * Team tasks controller.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage controllers
 */
class TeamTasksController extends TeamsController
{
    /**
     * Show user assignments.
     *
     * @param  Request             $request
     * @param  User                $user
     * @return ModelCollection|int
     */
    public function index(Request $request, User $user)
    {
        return $this->active_team->isLoaded() && $this->active_team->canView($user) ? Teams::prepareCollection('open_assignments_for_team_' . $this->active_team->getId(), $user) : Response::NOT_FOUND;
    }
}
