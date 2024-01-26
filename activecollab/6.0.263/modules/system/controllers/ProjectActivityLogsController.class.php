<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('project', SystemModule::NAME);

/**
 * Project activity logs controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class ProjectActivityLogsController extends ProjectController
{
    /**
     * List project activities.
     */
    public function index()
    {
    }

    /**
     * Project activities as RSS feed.
     */
    public function rss()
    {
    }
}
