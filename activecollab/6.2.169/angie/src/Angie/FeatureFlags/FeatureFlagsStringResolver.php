<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\FeatureFlags;

class FeatureFlagsStringResolver implements FeatureFlagsStringResolverInterface
{
    private $on_demand;
    private $in_production;
    private $in_development;

    public function __construct(
        bool $on_demand = false,
        bool $in_production = false,
        bool $in_development = false
    )
    {
        $this->on_demand = $on_demand;
        $this->in_production = $in_production;
        $this->in_development = $in_development;
    }

    public function getString(): string
    {
        if ($this->on_demand && $this->in_production) {
            return (string) getenv('ACTIVECOLLAB_FEATURE_FLAGS');
        } elseif ($this->in_development) {
            return (string) IS_LEGACY_DEV ? implode(',', defined('ACTIVECOLLAB_FEATURE_FLAGS') ? ACTIVECOLLAB_FEATURE_FLAGS : []) : (string) getenv('ACTIVECOLLAB_FEATURE_FLAGS');
        }

        return '';
    }
}
