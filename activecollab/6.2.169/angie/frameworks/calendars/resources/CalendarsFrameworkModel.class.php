<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

/**
 * Calendars framework model definition.
 *
 * @package angie.frameworks.calendars
 * @subpackage models
 */
class CalendarsFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct calendar framework model definition.
     *
     * @param CalendarsFramework $parent
     */
    public function __construct(CalendarsFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(
            DB::createTable('calendars')->addColumns(
                [
                    new DBIdColumn(),
                    DBTypeColumn::create('UserCalendar'),
                    DBNameColumn::create(255),
                    DBStringColumn::create('color', 7),
                    new DBAdditionalPropertiesColumn(),
                    new DBCreatedOnByColumn(true),
                    new DBUpdatedOnColumn(),
                    DBTrashColumn::create(),
                    DBIntegerColumn::create('position', 10, '0')->setUnsigned(true),
                ]
            )->addIndices(
                [
                    DBIndex::create('position'),
                ]
            )
        )
            ->setOrderBy('position')
            ->implementTrash()
            ->setTypeFromField('type')
            ->implementMembers(true)
            ->implementHistory()
            ->implementActivityLog()
            ->implementActivityLog()
            ->addModelTrait(ICalendarFeed::class, ICalendarFeedImplementation::class)
            ->addModelTrait(RoutingContextInterface::class, RoutingContextImplementation::class);

        $this->addModel(
            DB::createTable('calendar_events')->addColumns(
                [
                    new DBIdColumn(),
                    DBIntegerColumn::create('calendar_id', DBColumn::NORMAL, 0)->setUnsigned(true),
                    DBNameColumn::create(255),
                    DBDateColumn::create('starts_on'),
                    DBTimeColumn::create('starts_on_time'),
                    DBDateColumn::create('ends_on'),
                    DBTimeColumn::create('ends_on_time'),
                    DBEnumColumn::create('repeat_event', ['dont', 'daily', 'weekly', 'monthly', 'yearly'], 'dont'),
                    DBDateColumn::create('repeat_until'),
                    new DBAdditionalPropertiesColumn(),
                    new DBCreatedOnByColumn(true),
                    new DBUpdatedOnColumn(),
                    DBTrashColumn::create(true),
                    DBTextColumn::create('note')->setSize(DBTextColumn::BIG), // Keep it simple, this need to be plain text
                    DBIntegerColumn::create('position', 10, '0')->setUnsigned(true),
                ]
            )->addIndices(
                [
                    DBIndex::create('starts_on'),
                    DBIndex::create('starts_on_time', DBIndex::KEY, ['starts_on', 'starts_on_time']),
                    DBIndex::create('ends_on'),
                    DBIndex::create('ends_on_time', DBIndex::KEY, ['ends_on', 'ends_on_time']),
                    DBIndex::create('position'),
                ]
            )
        )
            ->setOrderBy('starts_on, starts_on_time, position')
            ->implementTrash()
            ->implementSubscriptions()
            ->implementAccessLog()
            ->implementHistory()
            ->implementActivityLog()
            ->addModelTrait(RoutingContextInterface::class, RoutingContextImplementation::class);

        $this->addTable(DB::createTable('calendar_users')->addColumns([
            DBIntegerColumn::create('user_id', DBColumn::NORMAL, 0),
            DBIntegerColumn::create('calendar_id', DBColumn::NORMAL, 0),
        ])->addIndices([
            new DBIndexPrimary(['user_id', 'calendar_id']),
        ]));
    }

    /**
     * Load initial data.
     */
    public function loadInitialData()
    {
        $this->addConfigOption('hidden_calendars');
        $this->addConfigOption('hidden_projects_on_calendar');
        $this->addConfigOption('calendar_sidebar_hidden');
        $this->addConfigOption('default_project_calendar_filter', 'everything_in_my_projects');
        $this->addConfigOption('calendar_mode', 'monthly');

        parent::loadInitialData();
    }
}
