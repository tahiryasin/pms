<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Utils;

use ActiveCollab\ActiveCollabJobs\Jobs\Instance\Job;
use ActiveCollab\ActiveCollabJobs\Jobs\Instance\StopwatchMaintenanceJob;
use ActiveCollab\Module\Tracking\Services\StopwatchServiceInterface;
use AngieApplication;
use DateTimeValue;
use SystemModule;

class StopwatchesMaintenance implements StopwatchesMaintenanceInterface
{
    private $manager;

    private $stopwatches_for_daily = [];

    private $stopwatches_for_maximum = [];

    /**
     * @var float
     */
    private $global_daily_capacity;

    /**
     * @var DateTimeValue
     */
    private $current_datetime;

    public function __construct(StopwatchManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->current_datetime = new DateTimeValue('now');
    }

    public function run(): void
    {
        $instance_id = AngieApplication::getAccountId();
        foreach($this->stopwatches_for_daily as $item){
            $delay = $this->calculateDelayForDailyCapacity($item);
            $this->createJob($instance_id, $item, $delay);
        }
        foreach ($this->stopwatches_for_maximum as $item){
            $delay = $this->calculateDelayForStopwatchMaximum($item);
            $this->createJob($instance_id, $item, $delay);
        }
    }

    private function createJob(int $instance_id, array $item, int $delay) {
        $data = [
            'priority' => Job::HAS_HIGHEST_PRIORITY,
            'instance_id' => $instance_id,
            'instance_type' => Job::FEATHER,
            'delay' => $delay,
            'date' => DateTimeValue::now()->format('Y-m-d'),
            'attempts' => 3,
            'user_id' => $item['user_id'],
            'stopwatch_id' => $item['id'],
            'user_email' => $item['user_email'],
            'ondemand' => AngieApplication::isOnDemand(),
        ];
        AngieApplication::jobs()->dispatch(new StopwatchMaintenanceJob($data), SystemModule::MAINTENANCE_JOBS_QUEUE_CHANNEL);
    }

    public function shouldRun(): bool
    {
        if(AngieApplication::isOnDemand()){
            if(
                AngieApplication::accountSettings()->getAccountStatus()->isSuspended() ||
                AngieApplication::accountSettings()->getAccountStatus()->isRetired()
            ){
                return false;
            }
        }

        return count($this->stopwatches_for_daily) > 0 || count($this->stopwatches_for_maximum) > 0;
    }

    public function calculateDelayForDailyCapacity(array $stopwatch): int
    {
        $seconds = $stopwatch['daily_capacity'] ? ((float) $stopwatch['daily_capacity'] * 3600) : ($this->global_daily_capacity * 3600);
        $date = new DateTimeValue($stopwatch['started_on']);
        $date->advance($seconds);
        if($this->current_datetime->getTimestamp() >= $date->getTimestamp()){
            return 1; //must be positive
        }
        $delay = abs($date->getTimestamp() - $this->current_datetime->getTimestamp());

        return $delay;
    }

    public function calculateDelayForStopwatchMaximum(array $stopwatch): int
    {
        $date = new DateTimeValue($stopwatch['started_on']);
        $limit = StopwatchServiceInterface::STOPWATCH_MAXIMUM;
        $elapsed = $stopwatch['elapsed'] + ($this->current_datetime->getTimestamp() - $date->getTimestamp());
        if ($elapsed > $limit) {
            return 1;
        }
        $delay = abs($limit - $elapsed);

        return $delay > 0 ? $delay : 1;
    }

    public function getForMaintenance(): StopwatchesMaintenanceInterface
    {
        $this->stopwatches_for_daily = $this->manager->findStopwatchesForDailyCapacityNotification();
        $this->stopwatches_for_maximum = $this->manager->findStopwatchesForMaximumCapacityNotification();
        $this->global_daily_capacity = $this->manager->getGlobalUserDailyCapacity();

        return $this;
    }

    public function setCurrentDatetime(DateTimeValue $current_datetime): StopwatchesMaintenance
    {
        $this->current_datetime = $current_datetime;

        return $this;
    }

    public function setGlobalDailyCapacity(float $global_daily_capacity): StopwatchesMaintenance
    {
        $this->global_daily_capacity = $global_daily_capacity;

        return $this;
    }
}
