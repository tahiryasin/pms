<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class MigrateOnDemandHelpdeskConversations.
 *
 * Helpdesk On Demand table
 *
 * @package activecollab.modules.on_Demand
 */
class MigrateOnDemandHelpdeskConversations extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        if (!$this->tableExists('helpdesk_conversations')) {
            $this->createTable('helpdesk_conversations', [
                new DBIdColumn(),
                DBIntegerColumn::create('ticket_id', 11),
                DBStringColumn::create('subject', 200, ''),
                DBBodyColumn::create(),
                DBIntegerColumn::create('status', 3, '0')->setUnsigned(true),
                DBIntegerColumn::create('state', 3, '0')->setUnsigned(true),
                DBIntegerColumn::create('original_state', 3)->setUnsigned(true),
                new DBCreatedOnByColumn(),
                DBDateTimeColumn::create('completed_on'),
                DBIntegerColumn::create('completed_by_id', 11),
                DBStringColumn::create('completed_by_name', 150),
                DBStringColumn::create('completed_by_email', 150),
                DBBoolColumn::create('is_urgent', false),
            ]);
        }
    }
}
