<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\OnDemand\Models\Pricing\PricingModelInterface;

/**
 * @package ActiveCollab.migrations
 */
class MigrateLegacyPlanTaskEstimatesEnabledLock extends AngieModelMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (AngieApplication::isOnDemand()) {
            $pricing_model = AngieApplication::shepherdAccountConfig()->getPricingModel(
                AngieApplication::getAccountId()
            );

            if ($pricing_model != PricingModelInterface::PRICING_MODEL_PER_SEAT_2018) {
                if (ConfigOptions::exists('task_estimates_enabled_lock')) {
                    $this->setConfigOptionValue('task_estimates_enabled_lock', false);
                }
            }
        }
    }
}
