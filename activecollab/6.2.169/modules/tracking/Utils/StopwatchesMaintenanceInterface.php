<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tracking\Utils;

interface StopwatchesMaintenanceInterface
{
    public function shouldRun(): bool;

    public function run(): void;

    public function getForMaintenance(): self;

    public function calculateDelayForDailyCapacity(array $stopwatch): int;

    public function calculateDelayForStopwatchMaximum(array $stopwatch): int;
}
