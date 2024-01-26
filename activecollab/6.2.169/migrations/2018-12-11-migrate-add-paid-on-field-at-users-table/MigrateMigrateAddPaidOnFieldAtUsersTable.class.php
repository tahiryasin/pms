<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateMigrateAddPaidOnFieldAtUsersTable extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->tableExists('users')) {
            $users = $this->useTableForAlter('users');

            if (!$users->getColumn('paid_on')) {
                $users->addColumn(DBDateTimeColumn::create('paid_on'), 'first_login_on');
            }

            $this->doneUsingTables();
        }
    }
}
