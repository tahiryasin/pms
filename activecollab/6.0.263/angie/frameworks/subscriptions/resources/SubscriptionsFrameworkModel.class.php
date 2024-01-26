<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Subscriptions framework model definition.
 *
 * @package angie.frameworks.subscriptions
 * @subpackage resources
 */
class SubscriptionsFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct subscriptions framework model definition.
     *
     * @param SubscriptionsFramework $parent
     */
    public function __construct(SubscriptionsFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(DB::createTable('subscriptions')->addColumns([
            new DBIdColumn(),
            new DBParentColumn(),
            DBUserColumn::create('user'),
            DBDateTimeColumn::create('subscribed_on'),
            DBStringColumn::create('code', 10),
        ])->addIndices([
            DBIndex::create('user_subscribed', DBIndex::UNIQUE, ['user_email', 'parent_type', 'parent_id']),
            DBIndex::create('subscribed_on', DBIndex::KEY, 'subscribed_on'),
        ]));
    }
}
