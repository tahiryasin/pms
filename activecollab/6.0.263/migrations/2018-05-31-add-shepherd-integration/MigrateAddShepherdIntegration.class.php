<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\OnDemand\Utils\ShepherdIntegration\ShepherdIntegration;

class MigrateAddShepherdIntegration extends AngieModelMigration
{
    public function up()
    {
        if (AngieApplication::isOnDemand()) {
            /** @var ShepherdIntegration $integration */
            $integration = Integrations::findFirstByType(ShepherdIntegration::class);

            if ($integration && !$integration->isInUse()) {
                $integration->initialize();
            }
        }
    }
}
