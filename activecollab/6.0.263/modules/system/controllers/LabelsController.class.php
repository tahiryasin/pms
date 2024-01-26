<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;

AngieApplication::useController('fw_labels', LabelsFramework::NAME);

/**
 * Application level controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class LabelsController extends FwLabelsController
{
    /**
     * Return a list of project labels.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function project_labels(Request $request, User $user)
    {
        return Labels::prepareCollection('project_labels', $user);
    }

    /**
     * Return a list of task labels.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function task_labels(Request $request, User $user)
    {
        return Labels::prepareCollection('task_labels', $user);
    }
}
