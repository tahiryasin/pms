<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Model\FeaturePointer;

use ActiveCollab\Module\OnDemand\Models\Pricing\PricingModelInterface;
use AngieApplication;
use FeaturePointer;
use User;

class AddOnsFeaturePointer extends FeaturePointer
{
    public function shouldShow(User $user): bool
    {
        return AngieApplication::isOnDemand() && $user->isOwner() && parent::shouldShow($user);
    }

    public function getDescription(): string
    {
        if (!AngieApplication::isOnDemand()) {
            return '';
        }

        $pricing_model = AngieApplication::shepherdAccountConfig()
            ->getPricingModel(AngieApplication::getAccountId());

        if ($pricing_model === PricingModelInterface::PRICING_MODEL_PLANS_2013) {
            return lang('We have new features in-store! Only available with the Per Seat pricing.');
        }

        return lang("We have new features in-store, and they're part of our Get Paid bundle!");
    }
}
