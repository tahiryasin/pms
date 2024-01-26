<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tasks\Utils\TaskDependencyNotificationDispatcher;

use Task;

interface TaskDependencyNotificationDispatcherInterface
{
    public function dispatchCompletedNotifications(Task $task): void;

    public function removeCompletedNotifications(Task $task): void;
}
