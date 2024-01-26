<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigratePusherIntegration extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->executeAfter('MigrateWebhooksToUseInstanceClassNameFromField');
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (AngieApplication::isOnDemand()) {
            Integrations::findFirstByType(PusherIntegration::class);
        }
    }
}
