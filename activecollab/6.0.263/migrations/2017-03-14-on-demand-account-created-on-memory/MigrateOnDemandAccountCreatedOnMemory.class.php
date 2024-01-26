<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.migrations
 */
class MigrateOnDemandAccountCreatedOnMemory extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (AngieApplication::isOnDemand()) {
            AngieApplication::memories()->set('account_created_on', Users::findFirstOwner()->getCreatedOn()->getTimestamp());
            AngieApplication::memories()->set('lead_survey_skipped', true);
        }
    }
}
