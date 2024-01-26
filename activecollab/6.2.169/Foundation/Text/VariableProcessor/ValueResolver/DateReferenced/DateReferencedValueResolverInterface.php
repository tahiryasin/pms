<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\DateReferenced;

use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\ValueResolverInterface;
use DateValue;

interface DateReferencedValueResolverInterface extends ValueResolverInterface
{
    public function mustGetReferenceDate(): DateValue;
}
