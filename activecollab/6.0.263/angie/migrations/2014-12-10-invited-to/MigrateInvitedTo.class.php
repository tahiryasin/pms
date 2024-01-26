<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Add invited to and drop invitation when accepted, instead of logging acceptance.
 *
 * @package angie.migrations
 */
class MigrateInvitedTo extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $invitations = $this->useTableForAlter('user_invitations');

        $invitations->addColumn(DBRelatedObjectColumn::create('invited_to', false), 'user_id');
        $invitations->addColumn(new DBUpdatedOnColumn(), 'invited_on');
        $invitations->addColumn(new DBCreatedOnByColumn(), 'invited_on');

        $invitations->dropColumn('invited_on');
        $invitations->dropColumn('accepted_on');

        $this->doneUsingTables();
    }
}
