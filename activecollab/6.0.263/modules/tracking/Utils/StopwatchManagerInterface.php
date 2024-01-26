<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Utils;

use ModelCollection;
use Stopwatch;
use User;

interface StopwatchManagerInterface
{
    public function create(array $attributes): Stopwatch;

    public function update(Stopwatch $stopwatch, array $attributes): Stopwatch;

    public function getRunningStopwatchForUser(int $user_id): ?Stopwatch;

    public function findOneByUserAndId(int $user_id, int $id): ?Stopwatch;

    public function getStopwatchesForUser(User $user): ModelCollection;
}
