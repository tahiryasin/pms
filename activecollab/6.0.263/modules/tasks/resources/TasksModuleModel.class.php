<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

require_once APPLICATION_PATH . '/resources/ActiveCollabModuleModel.class.php';

/**
 * Tasks module model.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
class TasksModuleModel extends ActiveCollabModuleModel
{
    /**
     * Construct tasks module model.
     *
     * @param TasksModule $parent
     */
    public function __construct(TasksModule $parent)
    {
        parent::__construct($parent);

        $this->addModel(
            DB::createTable('task_lists')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBIntegerColumn::create('project_id', 10, 0)->setUnsigned(true),
                        DBNameColumn::create(150),
                        DBDateColumn::create('start_on'),
                        DBDateColumn::create('due_on'),
                        DBActionOnByColumn::create('completed', true),
                        new DBCreatedOnByColumn(true, true),
                        new DBUpdatedOnByColumn(),
                        DBTrashColumn::create(true),
                        DBIntegerColumn::create('position', 10, 0)->setUnsigned(true),
                    ]
                )->addIndices(
                    [
                        DBIndex::create('project_id'),
                        DBIndex::create('span', DBIndex::KEY, ['start_on', 'due_on']),
                        DBIndex::create('due_on'),
                    ]
                )
        )
            ->setOrderBy('position')
            ->implementHistory()
            ->implementTrash()
            ->implementComplete()
            ->implementSearch()
            ->implementActivityLog()
            ->addModelTrait(IProjectElement::class, IProjectElementImplementation::class)
            ->addModelTrait(IWhoCanSeeThis::class, IWhoCanSeeThisImplementation::class)
            ->addModelTrait(IInvoiceBasedOn::class, IInvoiceBasedOnTrackedDataImplementation::class)
            ->addModelTraitTweak('IProjectElementImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation');

        $this->addModel(
            DB::createTable('tasks')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBFkColumn::create('project_id', 0, true),
                        DBIntegerColumn::create('task_number', 10, 0)->setUnsigned(true),
                        DBFkColumn::create('task_list_id', 0, true),
                        DBFkColumn::create('assignee_id', 0, true),
                        DBFkColumn::create('delegated_by_id', 0, true),
                        DBFkColumn::create('created_from_recurring_task_id', 0, true),
                        DBNameColumn::create(150),
                        DBBodyColumn::create(),
                        DBBoolColumn::create('is_important'),
                        new DBCreatedOnByColumn(true, true),
                        new DBUpdatedOnByColumn(),
                        DBDateColumn::create('start_on'),
                        DBDateColumn::create('due_on'),
                        DBFkColumn::create('job_type_id')->setSize(DBColumn::SMALL),
                        DBDecimalColumn::create('estimate', 12, 2, 0)->setUnsigned(true),
                        DBActionOnByColumn::create('completed'),
                        DBIntegerColumn::create('position', 10, 0)->setUnsigned(true),
                        DBBoolColumn::create('is_hidden_from_clients'),
                        DBTrashColumn::create(true),
                        DBStringColumn::create('fake_assignee_name'),
                        DBStringColumn::create('fake_assignee_email'),
                    ]
                )->addIndices(
                    [
                        DBIndex::create(
                            'project_task_number',
                            DBIndex::UNIQUE,
                            [
                                'project_id',
                                'task_number',
                            ]
                        ),
                        DBIndex::create('task_number'),
                        DBIndex::create('start_on'),
                        DBIndex::create('due_on'),
                    ]
                )
        )
            ->implementAssignees()
            ->implementComplete()
            ->implementHistory()
            ->implementAccessLog()
            ->implementComments(true, true)
            ->implementAttachments()
            ->implementLabels()
            ->implementTrash()
            ->implementSearch()
            ->implementActivityLog()
            ->implementReminders()
            ->addModelTrait(IHiddenFromClients::class)
            ->addModelTrait(ITaskDependencies::class, ITaskDependenciesImplementation::class)
            ->addModelTrait(IWhoCanSeeThis::class, IWhoCanSeeThisImplementation::class)
            ->addModelTrait(IProjectElement::class, IProjectElementImplementation::class)
            ->addModelTrait(ITracking::class, ITrackingImplementation::class)
            ->addModelTrait(IInvoiceBasedOn::class, IInvoiceBasedOnTrackedDataImplementation::class)
            ->addModelTraitTweak('IProjectElementImplementation::canViewAccessLogs insteadof IAccessLogImplementation')
            ->addModelTraitTweak('IProjectElementImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation');

        $this->addModel(DB::createTable('task_dependencies')->addColumns([
            new DBIdColumn(),
            DBFkColumn::create('parent_id', 0, true),
            DBFkColumn::create('child_id', 0, true),
            new DBCreatedOnColumn(),
        ])->addIndices([
            DBIndex::create('parent_id'),
            DBIndex::create('child_id'),
        ]));

        $this->addModel(
            DB::createTable('subtasks')
                ->addColumns(
                    [
                        new DBIdColumn(),
                        DBIntegerColumn::create('task_id', 10, 0)->setUnsigned(true),
                        DBIntegerColumn::create('assignee_id', 10, 0)->setUnsigned(true),
                        DBIntegerColumn::create('delegated_by_id', 10, 0)->setUnsigned(true),
                        DBTextColumn::create('body')->setSize(DBTextColumn::BIG),
                        DBDateColumn::create('due_on'),
                        new DBCreatedOnByColumn(true),
                        new DBUpdatedOnColumn(),
                        DBActionOnByColumn::create('completed', true),
                        DBIntegerColumn::create('position', 10, '0')->setUnsigned(true),
                        DBTrashColumn::create(true),
                        DBStringColumn::create('fake_assignee_name'),
                        DBStringColumn::create('fake_assignee_email'),
                    ]
                )
                ->addIndices(
                    [
                        DBIndex::create('task_id'),
                        DBIndex::create('created_on'),
                        DBIndex::create('position'),
                        DBIndex::create('completed_on'),
                        DBIndex::create('due_on'),
                        DBIndex::create('assignee_id'),
                        DBIndex::create('delegated_by_id'),
                    ]
                )
        )
            ->implementAssignees()
            ->implementComplete()
            ->implementHistory()
            ->implementTrash()
            ->implementActivityLog()
            ->setOrderBy('ISNULL(position) ASC, position, created_on');

        $this->addModel(
            DB::createTable('recurring_tasks')->addColumns(
                [
                    new DBIdColumn(),
                    DBFkColumn::create('project_id', 0, true),
                    DBFkColumn::create('task_list_id', 0, true),
                    DBFkColumn::create('assignee_id', 0, true),
                    DBFkColumn::create('delegated_by_id', 0, true),
                    DBNameColumn::create(150),
                    DBBodyColumn::create(),
                    DBBoolColumn::create('is_important'),
                    new DBCreatedOnByColumn(true, true),
                    new DBUpdatedOnByColumn(),
                    DBIntegerColumn::create('start_in')->setUnsigned(true),
                    DBIntegerColumn::create('due_in')->setUnsigned(true),
                    DBFkColumn::create('job_type_id')->setSize(DBColumn::SMALL),
                    DBDecimalColumn::create('estimate', 12, 2, 0)->setUnsigned(true),
                    DBIntegerColumn::create('position', 10, 0)->setUnsigned(true),
                    DBBoolColumn::create('is_hidden_from_clients'),
                    DBTrashColumn::create(true),
                    new DBEnumColumn(
                        'repeat_frequency',
                        [
                            'never',
                            'daily',
                            'weekly',
                            'monthly',
                            'quarterly',
                            'semiyearly',
                            'yearly',
                        ],
                        'never'
                    ),
                    (new DBIntegerColumn('repeat_amount', 10, 0))->setUnsigned(true),
                    (new DBIntegerColumn('repeat_amount_extended', 10, 0))->setUnsigned(true),
                    (new DBIntegerColumn('triggered_number', 10, 0))->setUnsigned(true),
                    DBDateColumn::create('last_trigger_on'),
                    DBStringColumn::create('fake_assignee_name'),
                    DBStringColumn::create('fake_assignee_email'),
                    new DBAdditionalPropertiesColumn(),
                ]
            )
        )
            ->implementAssignees()
            ->implementHistory()
            ->implementAccessLog()
            ->implementSubscriptions()
            ->implementAttachments()
            ->implementLabels()
            ->implementSearch()
            ->implementTrash()
            ->implementActivityLog()
            ->addModelTrait(IHiddenFromClients::class)
            ->addModelTrait(ISubtasks::class, ISubtasksImplementation::class)
            ->addModelTrait(IProjectElement::class, IProjectElementImplementation::class)
            ->addModelTraitTweak('IProjectElementImplementation::canViewAccessLogs insteadof IAccessLogImplementation')
            ->addModelTraitTweak('IProjectElementImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation');

        $this->addTable(
            DB::createTable('custom_hourly_rates')
                ->addColumns(
                    [
                        new DBParentColumn(true, false),
                        (new DBIntegerColumn('job_type_id', DBColumn::NORMAL, 0))
                            ->setUnsigned(true),
                        (new DBMoneyColumn('hourly_rate', 0))
                            ->setUnsigned(true),
                    ]
                )
                ->addIndices(
                    [
                        new DBIndexPrimary(
                            [
                                'parent_type',
                                'parent_id',
                                'job_type_id',
                            ]
                        ),
                    ]
                )
        );

        // Add is_global field to labels model
        AngieApplicationModel::getTable('labels')
            ->addColumn(DBBoolColumn::create('is_global'), 'is_default');
    }

    /**
     * Load initial framework data.
     */
    public function loadInitialData()
    {
        $this->addConfigOption('task_options', []);
        $this->addConfigOption('show_project_id', false);
        $this->addConfigOption('show_task_id', false);
        $this->addConfigOption('task_estimates_enabled', false);
        $this->addConfigOption('task_estimates_enabled_lock', false);
        $this->addConfigOption('display_mode_project_tasks', 'list');
        $this->addConfigOption('skip_days_off_when_rescheduling', true);
        $this->addConfigOption('tasks_filter_status', 'open');

        DB::execute("ALTER TABLE tasks ADD created_from_discussion_id INT UNSIGNED NOT NULL DEFAULT '0'");

        $labels = [
            ['NEW', '#C3E799'],
            ['CONFIRMED', '#FBBB75'],
            ['WORKS FOR ME', '#C3E799'],
            ['DUPLICATE', '#C3E799'],
            ['WONT FIX', '#C3E799'],
            ['ASSIGNED', '#FF9C9C'],
            ['BLOCKED', '#DDDDDD'],
            ['IN PROGRESS', '#C3E799'],
            ['FIXED', '#BEACF9'],
            ['REOPENED', '#FF9C9C'],
            ['VERIFIED', '#C3E799'],
        ];

        $counter = 1;
        $to_insert = [];

        foreach ($labels as $label) {
            $to_insert[] = DB::prepare("('TaskLabel', ?, ?, ?, ?)", $label[0], $label[1], true, $counter++);
        }

        DB::execute('INSERT INTO labels (type, name, color, is_global, position) VALUES ' . implode(', ', $to_insert));

        parent::loadInitialData();
    }
}
