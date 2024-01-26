<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Localization\LanguageInterface;

class YearResolver extends DateReferencedValueResolver
{
    public function getAvailableVariableNames(): array
    {
        return [
            $this->getPrefixedVariableName('year'),
            $this->getPrefixedVariableName('year+1'),
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        return [
            $this->getPrefixedVariableName('year') => $this->mustGetReferenceDate()->getYear(),
            $this->getPrefixedVariableName('year+1') => $this->mustGetReferenceDate()->getYear() + 1,
        ];
    }
}
