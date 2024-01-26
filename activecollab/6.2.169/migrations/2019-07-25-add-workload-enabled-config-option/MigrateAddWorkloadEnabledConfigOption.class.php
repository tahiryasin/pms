<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddWorkloadEnabledConfigOption extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addConfigOption('workload_enabled', true);
    }
}
