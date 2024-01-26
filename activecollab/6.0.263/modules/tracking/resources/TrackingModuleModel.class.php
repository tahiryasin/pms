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
     *
     * @param TrackingModule $parent
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
                    ]
                )
        )
            ->setOrderBy('id')
            ->addModelTrait(ICreatedOn::class, ICreatedOnImplementation::class)
            ->addModelTrait(IUpdatedOn::class, IUpdateOnImplementation::class)
            ->addModelTrait(IWhoCanSeeThis::class, IWhoCanSeeThisImplementation::class);
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
        $this->addConfigOption('default_is_tracking_enabled', false);
        $this->addConfigOption('default_is_client_reporting_enabled', false);
        $this->addConfigOption('job_type_id');
        $this->addConfigOption('default_job_type_id');

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
