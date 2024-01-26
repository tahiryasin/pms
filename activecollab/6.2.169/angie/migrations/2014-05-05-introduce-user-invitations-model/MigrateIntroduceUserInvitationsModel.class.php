<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Introduce user invitations model.
 *
 * @package angie.migrations
 */
class MigrateIntroduceUserInvitationsModel extends AngieModelMigration
{
    /**
     * Migrate up.
     */
    public function up()
    {
        $this->createTable(DB::createTable('user_invitations')->addColumns([
            new DBIdColumn(),
            DBIntegerColumn::create('user_id', 10, '0')->setUnsigned(true),
            DBStringColumn::create('code', 20, ''),
            DBDateTimeColumn::create('invited_on'),
            DBDateTimeColumn::create('accepted_on'),
        ])->addIndices([
            DBIndex::create('user_id', DBIndex::UNIQUE),
        ]));

        $users = $this->useTableForAlter('users');

        $users->dropColumn('invited_on');

        [$config_options, $config_option_values] = $this->useTables('config_options', 'config_option_values');

        $this->execute("DELETE FROM $config_options WHERE name = 'welcome_message'");
        $this->execute("DELETE FROM $config_option_values WHERE name = 'welcome_message'");

        $this->doneUsingTables();
    }
}
