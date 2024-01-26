<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateAddTaskFilterStatus extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addConfigOption('tasks_filter_status', 'open');
    }
}
