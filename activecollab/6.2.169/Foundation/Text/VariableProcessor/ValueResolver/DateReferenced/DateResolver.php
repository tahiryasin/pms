<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Localization\LanguageInterface;
use ActiveCollab\Foundation\Wrappers\ConfigOptions\ConfigOptionsInterface;
use DateValue;

class DateResolver extends DateReferencedValueResolver
{
    private $config_options;
    private $default_date_format;

    public function __construct(
        ?DateValue $reference_date,
        ConfigOptionsInterface $config_options,
        string $default_date_format,
        string $prefix = ''
    )
    {
        parent::__construct($reference_date, $prefix);

        $this->config_options = $config_options;
        $this->default_date_format = $default_date_format;
    }

    public function getAvailableVariableNames(): array
    {
        return [
            $this->getPrefixedVariableName('date'),
            $this->getPrefixedVariableName('date+1'),
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        $date_format = $this->config_options->getValue('format_date');

        if (empty($date_format)) {
            $date_format = $this->default_date_format;
        }

        return [
            $this->getPrefixedVariableName('date') => $this->mustGetReferenceDate()->formatUsingStrftime($date_format),
            $this->getPrefixedVariableName('date+1') => $this->mustGetReferenceDate()
                ->addDays(1, false)
                    ->formatUsingStrftime($date_format),
        ];
    }
}
