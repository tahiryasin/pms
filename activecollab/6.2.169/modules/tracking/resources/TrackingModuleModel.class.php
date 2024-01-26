<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

require_once APPLICATION_PATH . '/resources/ActiveCollabModuleModel.class.php';

/**
 * Tracking module model definition.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage resources
 */
class TrackingModuleModel extends ActiveCollabModuleModel
{
    /**
     * Construct tracking module model definition.
     */
    public function __construct(TrackingModule $parent)
    {
        parent::__construct($parent);

        $this->addModel(
            DB::createTable('time_records')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        new DBParentColumn(),
                        DBFkColumn::create('invoice_item_id', 0, true),
                        DBIntegerColumn::create('job_type_id', 5, 0)->setUnsigned(true),
                        DBDateColumn::create('record_date'),
                        DBDecimalColumn::create('value', 12, 2),
                        DBUserColumn::create('user'),
                        DBTextColumn::create('summary'),
                        DBIntegerColumn::create('billable_status', 3, 0)->setUnsigned(true),
                        new DBCreatedOnByColumn(),
                        new DBUpdatedOnByColumn(),
                        DBTrashColumn::create(true),
                        DBEnumColumn::create(
                            'source',
                            [
                                'timer_app',
                                'built_in_timer',
                                'my_time',
                                'my_timesheet',
                                'task_sidebar',
                                'project_time',
                                'project_timesheet',
                                'api_consumer',
                                'unknown',
                            ],
                            'unknown'
                        ),
                        new DBMoneyColumn('internal_rate', 0),
                        new DBMoneyColumn('job_type_hourly_rate', 0),
                    ]
                )
                ->addIndices(
                    [
                        DBIndex::create('job_type_id'),
                        DBIndex::create('record_date'),
                    ]
                )
        )
            ->setOrderBy('record_date DESC, created_on DESC')
            ->implementTrash()
            ->implementHistory()
            ->implementActivityLog()
            ->addModelTrait(ITrackingObject::class, ITrackingObjectImplementation::class)
            ->addModelTrait(IWhoCanSeeThis::class, IWhoCanSeeThisImplementation::class)
            ->addModelTraitTweak('ITrackingObjectImplementation::getCreatedActivityLogInstance insteadof IActivityLogImplementation')
            ->addModelTraitTweak('ITrackingObjectImplementation::getUpdatedActivityLogInstance insteadof IActivityLogImplementation')
            ->addModelTraitTweak('ITrackingObjectImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation');

        $this->addModel(
            DB::createTable('job_types')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBNameColumn::create(100),
                        new DBMoneyColumn('default_hourly_rate', 0),
                        DBBoolColumn::create('is_default'),
                        new DBArchiveColumn(),
                        new DBUpdatedOnColumn(),
                    ]
                )
                ->addIndices(
                    [
                        DBIndex::create('name', DBIndex::UNIQUE, 'name'),
                    ]
                )
        )
            ->setOrderBy('name')
            ->implementArchive()
            ->addModelTrait(null, IResetInitialSettingsTimestamp::class)
            ->addModelTrait(RoutingContextInterface::class, RoutingContextImplementation::class);

        $this->addModel(
            DB::createTable('expenses')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        new DBParentColumn(),
                        DBFkColumn::create('invoice_item_id', 0, true),
                        DBIntegerColumn::create('category_id', 5, 0)->setUnsigned(true),
                        DBDateColumn::create('record_date'),
                        (new DBMoneyColumn('value', 0))
                            ->setUnsigned(true),
                        DBUserColumn::create('user'),
                        DBTextColumn::create('summary'),
                        DBIntegerColumn::create('billable_status', 3, '0')->setUnsigned(true),
                        new DBCreatedOnByColumn(),
                        new DBUpdatedOnByColumn(),
                        DBTrashColumn::create(true),
                    ]
                )->addIndices(
                    [
                        DBIndex::create('category_id'),
                        DBIndex::create('record_date'),
                    ]
                )
        )
            ->setOrderBy('record_date DESC, created_on DESC')
            ->implementTrash()
            ->implementHistory()
            ->implementActivityLog()
            ->addModelTrait(ITrackingObject::class, ITrackingObjectImplementation::class)
            ->addModelTrait(IWhoCanSeeThis::class, IWhoCanSeeThisImplementation::class)
            ->addModelTraitTweak('ITrackingObjectImplementation::getCreatedActivityLogInstance insteadof IActivityLogImplementation')
            ->addModelTraitTweak('ITrackingObjectImplementation::getUpdatedActivityLogInstance insteadof IActivityLogImplementation')
            ->addModelTraitTweak('ITrackingObjectImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation');

        $this->addModel(
            DB::createTable('expense_categories')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBNameColumn::create(100),
                        DBBoolColumn::create('is_default', false),
                        new DBArchiveColumn(),
                    ]
                )->addIndices(
                    [
                        DBIndex::create('name', DBIndex::UNIQUE, 'name'),
                    ]
                )
        )
            ->setOrderBy('name')
            ->implementArchive()
            ->addModelTrait(null, IResetInitialSettingsTimestamp::class)
            ->addModelTrait(RoutingContextInterface::class, RoutingContextImplementation::class);

        $this->addModel(
            DB::createTable('stopwatches')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        new DBParentColumn(),
                        DBUserColumn::create('user'),
                        DBDateTimeColumn::create('started_on'),
                        DBIntegerColumn::create('is_kept', 0)->setSize(DBColumn::TINY)->setDefault(0),
                        DBIntegerColumn::create('elapsed', 50, 0),
                        new DBCreatedOnColumn(),
                        new DBUpdatedOnColumn(),
                        DBDateTimeColumn::create('notification_sent_at'),
                    ]
                )
                ->addIndices(
                    [
                        DBIndex::create('parent_key_reference', DBIndex::UNIQUE, ['parent_type', 'parent_id', 'user_id']),
                    ]
                )
        )
            ->setOrderBy('id')
            ->addModelTrait(ICreatedOn::class, ICreatedOnImplementation::class)
            ->addModelTrait(IUpdatedOn::class, IUpdatedOnImplementation::class)
            ->addModelTrait(IWhoCanSeeThis::class, IWhoCanSeeThisImplementation::class);

        $this->addModel(
            DB::createTable('user_internal_rates')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBUserColumn::create('user'),
                        new DBCreatedOnByColumn(),
                        new DBDateColumn('valid_from'),
                        new DBMoneyColumn('rate', 0),
                        new DBUpdatedOnByColumn(),
                    ]
                )
        )
            ->setOrderBy('id')
            ->addModelTrait(ICreatedOn::class, ICreatedOnImplementation::class)
            ->addModelTrait(ICreatedBy::class, ICreatedByImplementation::class);

        $this->addModel(
            DB::createTable('budget_thresholds')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBIntegerColumn::create('project_id', 10, 0)->setUnsigned(true),
                        new DBEnumColumn(
                           'type',
                           [
                               'income',
                               'cost',
                               'profit',
                           ],
                           'income'),
                        DBIntegerColumn::create('threshold', 10, 0)->setUnsigned(true),
                        new DBCreatedOnByColumn(),
                    ]
                )
        );

        $this->addModel(
            DB::createTable('budget_thresholds_notifications')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBIntegerColumn::create('parent_id', 10, 0)->setUnsigned(true),
                        DBIntegerColumn::create('user_id', 10, 0)->setUnsigned(true),
                        new DBDateTimeColumn('sent_at'),
                    ]
                )
        );
    }

    /**
     * Load initial framework data.
     */
    public function loadInitialData()
    {
        $this->addConfigOption('display_mode_project_time', 'list');
        $this->addConfigOption('filter_period_tracking_report', 'monthly');
        $this->addConfigOption('filter_period_payments_report', 'monthly');
        $this->addConfigOption('time_report_mode', 'time_tracking');

        $this->addConfigOption('default_billable_status', 1);
        $this->addConfigOption('default_is_tracking_enabled', true);
        $this->addConfigOption('default_is_client_reporting_enabled', false);
        $this->addConfigOption('job_type_id');
        $this->addConfigOption('default_job_type_id');
        $this->addConfigOption('minimal_time_entry', 15);
        $this->addConfigOption('rounding_interval', 0);
        $this->addConfigOption('rounding_enabled', false);
        $this->addConfigOption('stopwatch_indicator_seen', false);
        $this->addConfigOption('time_record_description_expanded', false);
        $this->addConfigOption('default_project_budget_type', 'pay_as_you_go');
        $this->addConfigOption('default_tracking_objects_are_billable', true);
        $this->addConfigOption('default_members_can_change_billable', false);

        DB::execute('ALTER TABLE `time_records` ADD `summary_length` ENUM("empty", "short", "long") NOT NULL DEFAULT "empty" AFTER `summary`');

        DB::execute('CREATE TRIGGER summary_length_for_time_records_before_insert BEFORE INSERT ON `time_records` FOR EACH ROW
            BEGIN
                IF NEW.`summary` IS NULL OR NEW.`summary` = "" THEN
                    SET NEW.`summary_length` = "empty";
                ELSE
                    IF CHAR_LENGTH(NEW.`summary`) > 100 THEN
                        SET NEW.`summary_length` = "long";
                    ELSE
                        SET NEW.`summary_length` = "short";
                    END IF;
                END IF;
            END'
        );

        DB::execute('CREATE TRIGGER summary_length_for_time_records_before_update BEFORE UPDATE ON `time_records` FOR EACH ROW
            BEGIN
                IF NEW.`summary` = OLD.`summary` THEN
                    SET NEW.`summary_length` = OLD.`summary_length`;
                ELSE
                    IF NEW.`summary` IS NULL OR NEW.`summary` = "" THEN
                        SET NEW.`summary_length` = "empty";
                    ELSE
                        IF CHAR_LENGTH(NEW.`summary`) > 100 THEN
                            SET NEW.`summary_length` = "long";
                        ELSE
                            SET NEW.`summary_length` = "short";
                        END IF;
                    END IF;
                END IF;
            END'
        );

        $this->loadTableData(
            'job_types',
            [
                [
                    'name' => 'General',
                    'default_hourly_rate' => 100,
                    'is_default' => true,
                ],
            ]
        );
        $this->loadTableData(
            'expense_categories',
            [
                [
                    'name' => 'General',
                    'is_default' => true,
                ],
            ]
        );

        parent::loadInitialData();
    }
}
