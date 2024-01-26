<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\ProjectEvents\ProjectUpdatedEventInterface;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\ExpenseEvents\ExpenseLifeCycleEventInterface;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\StopwatchEvents\StopwatchLifeCycleEventInterface;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\TimeRecordEvents\TimeRecordLifeCycleEventInterface;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\UserInternalRateEvents\UserInternalRateLifeCycleEventInterface;
use ActiveCollab\Module\Tracking\Services\TrackingServiceInterface;

class TrackingModule extends AngieModule
{
    const NAME = 'tracking';

    protected $name = 'tracking';
    protected $version = '5.0';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            TimeRecord::class,
            function ($ids) {
                return TimeRecords::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            JobType::class,
            function ($ids) {
                return JobTypes::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Expense::class,
            function ($ids) {
                return Expenses::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            ExpenseCategory::class,
            function ($ids) {
                return ExpenseCategories::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        require_once __DIR__ . '/resources/autoload_model.php';

        AngieApplication::setForAutoload(
            [
                TrackingObjects::class => __DIR__ . '/models/tracking_objects/TrackingObjects.class.php',

                ITracking::class => __DIR__ . '/models/ITracking.php',
                ITrackingImplementation::class => __DIR__ . '/models/ITrackingImplementation.php',

                ITrackingObject::class => __DIR__ . '/models/tracking_objects/ITrackingObject.php',
                ITrackingObjectImplementation::class => __DIR__ . '/models/tracking_objects/ITrackingObjectImplementation.php',
                ITrackingObjectsImplementation::class => __DIR__ . '/models/tracking_objects/ITrackingObjectsImplementation.php',

                TrackingFilter::class => __DIR__ . '/models/reports/TrackingFilter.php',

                ITrackingObjectActivityLog::class => __DIR__ . '/models/activity_logs/ITrackingObjectActivityLog.php',
                TrackingObjectCreatedActivityLog::class => __DIR__ . '/models/activity_logs/TrackingObjectCreatedActivityLog.php',
                TrackingObjectUpdatedActivityLog::class => __DIR__ . '/models/activity_logs/TrackingObjectUpdatedActivityLog.php',

                TimeRecordsCollection::class => __DIR__ . '/models/time_record_collections/TimeRecordsCollection.php',
                ProjectTimeRecordsCollection::class => __DIR__ . '/models/time_record_collections/ProjectTimeRecordsCollection.php',
                TaskTimeRecordsCollection::class => __DIR__ . '/models/time_record_collections/TaskTimeRecordsCollection.php',
                UserTimeRecordsCollection::class => __DIR__ . '/models/time_record_collections/UserTimeRecordsCollection.php',
                TimesheetReportCollection::class => __DIR__ . '/models/time_record_collections/TimesheetReportCollection.php',
                UserTimesheetReportCollection::class => __DIR__ . '/models/time_record_collections/UserTimesheetReportCollection.php',

                ExpensesCollection::class => __DIR__ . '/models/expense_collections/ExpensesCollection.php',
                ProjectExpensesCollection::class => __DIR__ . '/models/expense_collections/ProjectExpensesCollection.php',
                TaskExpensesCollection::class => __DIR__ . '/models/expense_collections/TaskExpensesCollection.php',

                TimerIntegration::class => __DIR__ . '/models/integrations/TimerIntegration.php',

                StopwatchesCollection::class => __DIR__ . '/models/StopwatchesCollection.php',

                //Notifications
                StopwatchDailyCapacityExceedNotification::class => __DIR__ .'/notifications/StopwatchDailyCapacityExceedNotification.class.php',
                StopwatchMaximumReachedNotification::class => __DIR__ . '/notifications/StopwatchMaximumReachedNotification.class.php',
                BudgetThresholdReachedNotification::class => __DIR__ . '/notifications/BudgetThresholdReachedNotification.class.php',
            ]
        );
    }

    public function defineHandlers()
    {
        $this->listen('on_rebuild_activity_logs');
        $this->listen('on_trash_sections');
        $this->listen('on_initial_settings');
        $this->listen('on_resets_initial_settings_timestamp');
        $this->listen('on_initial_collections');
        $this->listen('on_initial_user_collections');
        $this->listen('on_protected_config_options');
        $this->listen('on_available_integrations');
        $this->listen('on_visible_object_paths');
        $this->listen('on_extra_stats');
        $this->listen('on_hourly_maintenance');
    }

    public function defineListeners(): array
    {
        return [
            UserInternalRateLifeCycleEventInterface::class => function (UserInternalRateLifeCycleEventInterface $event) {
                AngieApplication::getContainer()->get(TrackingServiceInterface::class)
                    ->setInternalRateForUserTimeRecords($event->getObject(), $event->getWebhookEventType());
            },
            ProjectUpdatedEventInterface::class => function (ProjectUpdatedEventInterface $event) {
                AngieApplication::getContainer()->get(TrackingServiceInterface::class)
                    ->calcRatesForProjectTimeRecords($event->getObject());
            },
            StopwatchLifeCycleEventInterface::class => function ($event) {
                AngieApplication::socketsDispatcher()->dispatch($event->getObject(), $event->getWebhookEventType());
            },
            TimeRecordLifeCycleEventInterface::class => function (TimeRecordLifeCycleEventInterface $event) {
                AngieApplication::socketsDispatcher()->dispatch($event->getObject(), $event->getWebhookEventType());
            },
            ExpenseLifeCycleEventInterface::class => function ($event) {
                AngieApplication::socketsDispatcher()->dispatch($event->getObject(), $event->getWebhookEventType());
            },
        ];
    }
}
