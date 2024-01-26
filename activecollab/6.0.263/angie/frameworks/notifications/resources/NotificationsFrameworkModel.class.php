<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Notifications framework model definition.
 *
 * @package angie.frameworks.notifications
 * @subpackage resources
 */
class NotificationsFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct subscriptions framework model definition.
     *
     * @param NotificationsFramework $parent
     */
    public function __construct(NotificationsFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(DB::createTable('notifications')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create('Notification'),
            new DBParentColumn(),
            DBUserColumn::create('sender'),
            new DBCreatedOnColumn(),
            new DBAdditionalPropertiesColumn(),
        ])->addIndices([
            DBIndex::create('created_on'),
        ]))->setTypeFromField('type')->setObjectIsAbstract(true)->setOrderBy('created_on DESC, id DESC');

        $this->addTable(DB::createTable('notification_recipients')->addColumns([
            new DBIdColumn(),
            DBIntegerColumn::create('notification_id')->setUnsigned(true),
            DBUserColumn::create('recipient'),
            DBDateTimeColumn::create('read_on'),
            DBBoolColumn::create('is_mentioned', false),
        ])->addIndices([
            DBIndex::create('notification_recipient', DBIndex::UNIQUE, ['notification_id', 'recipient_email']),
        ]));
    }

    /**
     * Load initial framework data.
     */
    public function loadInitialData()
    {
        $this->addConfigOption('notifications_notify_email_sender', true);

        parent::loadInitialData();
    }
}
