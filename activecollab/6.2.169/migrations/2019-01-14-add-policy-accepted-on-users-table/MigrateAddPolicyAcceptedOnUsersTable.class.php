<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddPolicyAcceptedOnUsersTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->tableExists('users')) {
            $users = $this->useTableForAlter('users');

            if (!$users->getColumn('policy_accepted_on')) {
                $users->addColumn(DBDateTimeColumn::create('policy_accepted_on'), 'policy_version');
            }

            $this->doneUsingTables();
        }
    }
}
