<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.tasks
 * @subpackage resources
 */
AngieApplication::useModel(
    [
        'recurring_tasks',
        'subtasks',
        'task_dependencies',
        'task_lists',
        'tasks',
    ],
    'tasks'
);
