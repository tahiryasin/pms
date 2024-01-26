<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Localization\LanguageInterface;

class MonthDayResolver extends DateReferencedValueResolver
{
    public function getAvailableVariableNames(): array
    {
        return [
            $this->getPrefixedVariableName('month-day'),
            $this->getPrefixedVariableName('month-day+1'),
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        return [
            $this->getPrefixedVariableName('month-day') => $this->mustGetReferenceDate()->getDay(),
            $this->getPrefixedVariableName('month-day+1') => $this->mustGetReferenceDate()
                ->addDays(1, false)
                    ->getDay(),
        ];
    }
}
