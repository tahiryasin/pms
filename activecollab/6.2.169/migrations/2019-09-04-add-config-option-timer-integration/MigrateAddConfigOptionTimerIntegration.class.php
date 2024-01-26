<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class MigrateAddConfigOptionTimerIntegration extends AngieModelMigration
{
    public function up()
    {
        /**
         * @var TimerIntegration
         */
        $integration = Integrations::findFirstByType(TimerIntegration::class);

        $minimalTimeEntry = $integration->getAdditionalProperty('minimal_time_entry');
        $roundingInterval = $integration->getAdditionalProperty('rounding_interval');

        $this->addConfigOption('minimal_time_entry', $minimalTimeEntry);
        $this->addConfigOption('rounding_interval', $roundingInterval);
    }
}
