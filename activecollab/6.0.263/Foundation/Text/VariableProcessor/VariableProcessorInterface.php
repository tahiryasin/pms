<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor;

use ActiveCollab\Foundation\Localization\LanguageInterface;

interface VariableProcessorInterface
{
    public function getAvailableVariableNames(): array;
    public function process(string $text, LanguageInterface $language): string;
}
