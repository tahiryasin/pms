<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('projects', SystemModule::NAME);

/**
 * Single project controller.
 *
 * @package ActiveCollab.modules.system
 * @subpackage controllers
 */
abstract class ProjectController extends ProjectsController
{
    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if ($this->active_project->isNew() || !$this->active_project->canView($user)) {
            return Response::NOT_FOUND;
        }
    }
}
