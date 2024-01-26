<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Invoicing\Utils\VariableProcessor\Factory;

use ActiveCollab\Foundation\Text\VariableProcessor\VariableProcessorInterface;
use Invoice;

interface VariableProcessorFactoryInterface
{
    public function getAvailableVariableNamesForInvoice(): array;
    public function createFromInvoice(Invoice $invoice): VariableProcessorInterface;
}
