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
use Stopwatches;
use User;

class StopwatchManager implements StopwatchManagerInterface
{
    public function create(array $attributes): Stopwatch
    {
        return Stopwatches::create($attributes);
    }

    public function update(Stopwatch $stopwatch, array $attributes): Stopwatch
    {
        return Stopwatches::update($stopwatch, $attributes);
    }

    public function getRunningStopwatchForUser(int $user_id): ?Stopwatch
    {
        return Stopwatches::find([
            'conditions' => ['user_id = ? AND started_on IS NOT NULL', $user_id],
            'one' => true,
            'order' => '`id` DESC',
        ]);
    }

    public function findOneByUserAndId(int $user_id, int $id): ?Stopwatch
    {
        return Stopwatches::find([
            'conditions' => ['user_id = ? AND id = ?', $user_id, $id],
            'one' => true,
        ]);
    }

    public function getStopwatchesForUser(User $user): ModelCollection
    {
        return Stopwatches::prepareCollection('user_stopwatches_for_'.$user->getId(), $user);
    }
}
