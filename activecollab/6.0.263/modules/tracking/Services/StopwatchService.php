<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Services;

use ActiveCollab\EventsDispatcher\EventsDispatcherInterface;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\StopwatchEvents\StopwatchCreatedEvent;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\StopwatchEvents\StopwatchUpdatedEvent;
use ActiveCollab\Module\Tracking\Utils\StopwatchManagerInterface;
use ActiveCollab\User\UserInterface;
use DateTimeValue;
use InvalidArgumentException;
use Stopwatch;

class StopwatchService implements StopwatchServiceInterface
{
    private $dispatcher;
    private $manager;
    private $current_date;

    public function __construct(
        EventsDispatcherInterface $dispatcher,
        StopwatchManagerInterface $manager,
        DateTimeValue $current_date
    )
    {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->current_date = $current_date;
    }

    public function start(UserInterface $user, string $parent_type, int $parent_id): Stopwatch
    {
        if (!in_array($parent_type, self::ALLOWED_TYPES)) {
            throw new InvalidArgumentException(
                sprintf('Invalid type passed to service, allowed types are %s',
                    implode(',', self::ALLOWED_TYPES)
                )
            );
        }

        $running = $this->stopRunningStopwatch($user->getId());

        if (
            $running &&
            $running->getParentId() === $parent_id &&
            $running->getParentType() === $parent_type
        ) {
            throw new InvalidArgumentException(
                'Same stopwatch already running'
            );
        }

        $stopwatch = $this->manager->create(
            [
                'started_on' => $this->current_date,
                'user_id' => $user->getId(),
                'user_name' => $user->getFullName(),
                'user_email' => $user->getEmail(),
                'parent_type' => $parent_type,
                'parent_id' => $parent_id,
            ]
        );

        $this->dispatcher->trigger(new StopwatchCreatedEvent($stopwatch));

        return $stopwatch;
    }

    public function resume(Stopwatch $stopwatch): Stopwatch
    {
        $this->stopRunningStopwatch($stopwatch->getUserId());

        $stopwatch = $this->manager->update(
            $stopwatch,
            [
                'started_on' => $this->current_date,
            ]
        );

        $this->dispatcher->trigger(new StopwatchUpdatedEvent($stopwatch));

        return $stopwatch;
    }

    public function pause(Stopwatch $stopwatch): Stopwatch
    {
        $elapsed = $this->calculateElapsedTime($stopwatch);

        $stopwatch = $this->manager->update(
            $stopwatch,
            [
                'started_on' => null,
                'elapsed' => $elapsed,
            ]
        );

        $this->dispatcher->trigger(new StopwatchUpdatedEvent($stopwatch));

        return $stopwatch;
    }

    private function calculateElapsedTime(Stopwatch $stopwatch): int
    {
        $elapsed = abs($this->current_date->getTimestamp() - $stopwatch->getStartedOn()->getTimestamp());
        $elapsed += (int) $stopwatch->getElapsed();

        return $elapsed;
    }

    private function stopRunningStopwatch(int $user_id): ?Stopwatch
    {
        $running = $this->manager->getRunningStopwatchForUser($user_id);

        if ($running) {
            $elapsed = $this->calculateElapsedTime($running);
            $paused_stopwatch = $this->manager->update($running, [
                'started_on' => null,
                'elapsed' => $elapsed,
            ]);
            $this->dispatcher->trigger(new StopwatchUpdatedEvent($paused_stopwatch));
        }

        return $running;
    }
}
