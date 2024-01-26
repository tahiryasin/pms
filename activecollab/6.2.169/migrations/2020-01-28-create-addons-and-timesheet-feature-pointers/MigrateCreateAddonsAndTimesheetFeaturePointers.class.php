<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\OnDemand\Models\Pricing\PricingModelInterface;
use ActiveCollab\Module\System\Model\FeaturePointer\AddOnsFeaturePointer;
use ActiveCollab\Module\System\Model\FeaturePointer\TimesheetFeaturePointer;

class MigrateCreateAddonsAndTimesheetFeaturePointers extends AngieModelMigration
{
    public function up()
    {
        if (!AngieApplication::isOnDemand()) {
            $this->insertFeaturePointer(TimesheetFeaturePointer::class);
        } elseif (!AngieApplication::accountSettings()->getAccountStatus()->isTrial()) {
            $pricing_model = AngieApplication::shepherdAccountConfig()
                ->getPricingModel(AngieApplication::getAccountId());

            $per_seat_pricing = $pricing_model === PricingModelInterface::PRICING_MODEL_PER_SEAT_2018;

            if ($per_seat_pricing) {
                $add_ons = AngieApplication::shepherdAccountConfig()->getAddOns(AngieApplication::getAccountId());
                $has_get_paid_add_on = in_array('get_paid', $add_ons) || in_array('get_paid_full', $add_ons);

                if ($has_get_paid_add_on) {
                    $this->insertFeaturePointer(TimesheetFeaturePointer::class);
                } else {
                    $this->insertFeaturePointer(AddOnsFeaturePointer::class);
                }
            } else {
                $this->insertFeaturePointer(AddOnsFeaturePointer::class);
            }
        }
    }

    private function insertFeaturePointer($type)
    {
        $this->execute(
            'INSERT INTO feature_pointers (type, parent_id, created_on) VALUES (?, ?, ?)',
            $type,
            null,
            new DateTimeValue()
        );
    }
}
