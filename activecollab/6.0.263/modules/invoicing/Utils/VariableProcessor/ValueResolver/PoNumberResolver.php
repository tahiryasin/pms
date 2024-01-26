<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Invoicing\Utils\VariableProcessor\ValueResolver;

use ActiveCollab\Foundation\Localization\LanguageInterface;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\ValueResolverInterface;

class PoNumberResolver implements ValueResolverInterface
{
    /**
     * @var string|null
     */
    private $po_number;

    public function __construct(?string $po_number)
    {
        $this->po_number = $po_number;
    }

    public function getAvailableVariableNames(): array
    {
        return [
            'purchase-order-number',
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        return [
            'purchase-order-number' => $this->po_number,
        ];
    }
}
