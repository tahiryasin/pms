<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\FeatureFlags;

class FeatureFlags implements FeatureFlagsInterface
{
    private $feature_flags;
    private $account_id;
    private $distribution_channel;

    public function __construct(
        FeatureFlagsStringResolverInterface $resolver,
        int $account_id,
        string $distribution_channel
    )
    {
        $this->account_id = $account_id;
        $this->distribution_channel = $distribution_channel;

        $this->feature_flags = $this->parseFeatureFlagsString(trim($resolver->getString()));
    }

    public function getFeatureFlags(): array
    {
        return $this->feature_flags;
    }

    public function isEnabled(string $feature_flag): bool
    {
        return in_array($feature_flag, $this->feature_flags);
    }

    public function jsonSerialize()
    {
        return $this->feature_flags;
    }

    private function parseFeatureFlagsString(string $feature_flags_string): array
    {
        $result = [];

        if ($feature_flags_string) {
            $feature_flags = explode(',', $feature_flags_string);

            foreach ($feature_flags as $feature_flag) {
                $modifier = '';
                $modifier_pos = strpos($feature_flag, '/');

                if ($modifier_pos !== false) {
                    $modifier = substr($feature_flag, $modifier_pos + 1);
                    $feature_flag = substr($feature_flag, 0, $modifier_pos);
                }

                if ($this->shouldInclude($modifier)) {
                    $result[] = $feature_flag;
                }
            }
        }

        return $result;
    }

    private function shouldInclude(string $modifier): bool
    {
        if (empty($modifier)) {
            return true;
        }

        if (ctype_digit($modifier)) {
            return $this->shouldIncludeByAcccountId((int) $modifier);
        } else {
            return $this->shouldIncludeByChannel($modifier);
        }
    }

    private function shouldIncludeByChannel(string $modifier): bool
    {
        switch ($modifier) {
            case FeatureFlagsInterface::EDGE_CHANNEL_MODIFIER:
                return $modifier === $this->distribution_channel;

            case FeatureFlagsInterface::BETA_CHANNEL_MODIFIER:
                return $modifier === $this->distribution_channel
                    || $this->distribution_channel === FeatureFlagsInterface::EDGE_CHANNEL_MODIFIER;

            case FeatureFlagsInterface::STABLE_CHANNEL_MODIFIER:
                return true;

            default:
                return false;
        }
    }

    private function shouldIncludeByAcccountId(int $modifier): bool
    {
        if ($modifier === 1) {
            return $this->account_id % 2 > 0;
        } else {
            return empty($this->account_id % $modifier);
        }
    }
}
