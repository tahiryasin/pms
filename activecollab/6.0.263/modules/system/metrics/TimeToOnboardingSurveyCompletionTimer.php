<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\System\Metric;

use Angie\Metric\Result\ResultInterface;
use Angie\Metric\Timer;
use AngieApplication;
use DateValue;

class TimeToOnboardingSurveyCompletionTimer extends Timer
{
    /**
     * Get value of the given metric for the given date.
     *
     * @param  DateValue       $date
     * @return ResultInterface
     */
    public function getValueFor(DateValue $date)
    {
        $result = -1;

        $survey_skipped = AngieApplication::onboardingSurvey()->isLeadSurveySkipped();
        $survey_submitted_on = AngieApplication::onboardingSurvey()->getLeadSurveySubmittedOn();
        $account_created_on = AngieApplication::memories()->get('account_created_on');

        if (!$survey_skipped && !empty($account_created_on) && !empty($survey_submitted_on)) {
            $result = (int) $survey_submitted_on - (int) $account_created_on;
        }

        return $this->produceResult($result, $date);
    }
}
