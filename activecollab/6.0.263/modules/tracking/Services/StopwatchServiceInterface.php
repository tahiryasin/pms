<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Services;

use ActiveCollab\User\UserInterface;
use Project;
use Stopwatch;
use Task;

interface StopwatchServiceInterface
{
    const ALLOWED_TYPES = [
        Task::class,
        Project::class,
    ];

    public function start(UserInterface $user, string $parent_type, int $parent_id): Stopwatch;

    public function resume(Stopwatch $stopwatch): Stopwatch;

    public function pause(Stopwatch $stopwatch): Stopwatch;
}
