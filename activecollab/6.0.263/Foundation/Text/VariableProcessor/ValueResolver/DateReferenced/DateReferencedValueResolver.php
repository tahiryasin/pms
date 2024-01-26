<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\ValueResolver;
use DateValue;
use LogicException;

abstract class DateReferencedValueResolver extends ValueResolver implements DateReferencedValueResolverInterface
{
    private $reference_date;
    private $prefix;

    public function __construct(?DateValue $reference_date, string $prefix = '')
    {
        $this->reference_date = $reference_date;
        $this->prefix = $prefix;
    }

    public function getReferenceDate(): ?DateValue
    {
        return $this->reference_date;
    }

    public function mustGetReferenceDate(): DateValue
    {
        if (empty($this->reference_date)) {
            throw new LogicException('Reference date is not set.');
        }

        return $this->reference_date;
    }

    protected function getPrefixedVariableName(string $variable_name): string
    {
        return empty($this->prefix) ? $variable_name : sprintf('%s-%s', $this->prefix, $variable_name);
    }
}
