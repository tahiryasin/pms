<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Localization\LanguageInterface;

class QuarterResolver extends DateReferencedValueResolver
{
    public function getAvailableVariableNames(): array
    {
        return [
            $this->getPrefixedVariableName('quarter'),
            $this->getPrefixedVariableName('quarter+1'),
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        $current_quarter = $this->mustGetReferenceDate()->getQuarter();
        $next_quarter = $current_quarter < 4 ? $current_quarter + 1 : 1;

        return [
            $this->getPrefixedVariableName('quarter') => sprintf('Q%d', $current_quarter),
            $this->getPrefixedVariableName('quarter+1') => sprintf('Q%d', $next_quarter),
        ];
    }
}
