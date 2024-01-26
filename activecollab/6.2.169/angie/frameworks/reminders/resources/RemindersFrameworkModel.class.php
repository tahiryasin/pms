<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reminders framework model.
 *
 * @package angie.frameworks.reminders
 * @subpackage resources
 */
class RemindersFrameworkModel extends AngieFrameworkModel
{
    /**
     * Construct reminders framework model definition.
     *
     * @param RemindersFramework $parent
     */
    public function __construct(RemindersFramework $parent)
    {
        parent::__construct($parent);

        $this->addModel(DB::createTable('reminders')->addColumns([
            new DBIdColumn(),
            DBTypeColumn::create('CustomReminder', 50, false),
            new DBParentColumn(),
            DBDateColumn::create('send_on'),
            DBTextColumn::create('comment'),
            new DBCreatedOnByColumn(true, true),
        ]))->setTypeFromField('type')->implementSubscriptions();
    }
}
