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

class ClientNameResolver implements ValueResolverInterface
{
    private $client_name;

    public function __construct(?string $client_name)
    {
        $this->client_name = $client_name;
    }

    public function getAvailableVariableNames(): array
    {
        return [
            'client-name',
        ];
    }

    public function getVariableReplacements(LanguageInterface $language): array
    {
        return [
            'client-name' => $this->client_name,
        ];
    }
}
