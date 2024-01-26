<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Metric;

use Angie\Metric\Flag;
use Angie\Metric\FlagInterface;
use AngieApplication;
use DateValue;

class HasCompletedOnboardingSurveyFlag extends Flag implements FlagInterface
{
    public function getValueFor(DateValue $date)
    {
        $result = (bool) AngieApplication::onboardingSurvey()->getLeadSurveySubmittedOn() && !AngieApplication::onboardingSurvey()->isLeadSurveySkipped();

        return $this->produceResult($result, $date);
    }
}
