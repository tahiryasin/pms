<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Localization\LanguageInterface;

class MonthResolver extends DateReferencedValueResolver
{
    public function getAvailableVariableNames(): array
    {
        return [
            $this->getPrefixedVariableName('month'),
            $this->getPrefixedVariableName('month+1'),
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        $current_month = $this->mustGetReferenceDate()->getMonth();
        $next_month = $current_month < 12 ? $current_month + 1 : 1;

        return [
            $this->getPrefixedVariableName('month') => $current_month,
            $this->getPrefixedVariableName('month+1') => $next_month,
        ];
    }
}
