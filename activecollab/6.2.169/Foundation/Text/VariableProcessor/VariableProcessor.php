<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Text\VariableProcessor;

use ActiveCollab\Foundation\Localization\LanguageInterface;
use ActiveCollab\Foundation\Text\VariableProcessor\ValueResolver\ValueResolverInterface;

class VariableProcessor implements VariableProcessorInterface
{
    const OPEN_TAG = '{';
    const CLOSE_TAG = '}';

    private $resolvers;

    public function __construct(ValueResolverInterface ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function getAvailableVariableNames(): array
    {
        $result = [];

        foreach ($this->resolvers as $resolver) {
            $result = array_merge($result, $resolver->getAvailableVariableNames());
        }

        return $result;
    }

    public function process(string $text, LanguageInterface $language): string
    {
        $search = [];
        $replace = [];

        foreach ($this->resolvers as $resolver) {
            foreach ($resolver->getVariableReplacements($language) as $variable_name => $replacement_value) {
                $search[] = $this->getVariableTag($variable_name);
                $replace[] = $replacement_value;
            }
        }

        return str_replace($search, $replace, $text);
    }

    private function getVariableTag(string $variable_name): string
    {
        return sprintf('%s%s%s', self::OPEN_TAG, $variable_name, self::CLOSE_TAG);
    }
}
