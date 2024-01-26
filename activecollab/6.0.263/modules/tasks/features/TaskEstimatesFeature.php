<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use Angie\Features\Feature;

final class TaskEstimatesFeature extends Feature implements TaskEstimatesFeatureInterface
{
    public function getName(): string
    {
        return TaskEstimatesFeatureInterface::NAME;
    }

    public function getVerbose(): string
    {
        return TaskEstimatesFeatureInterface::VERBOSE_NAME;
    }

    public function activate(): bool
    {
        ConfigOptions::setValue('task_estimates_enabled', true, true);
        ConfigOptions::setValue('task_estimates_enabled_lock', false, true);

        return true;
    }

    public function deactivate(): bool
    {
        ConfigOptions::setValue('task_estimates_enabled', false, true);
        ConfigOptions::setValue('task_estimates_enabled_lock', true, true);

        return true;
    }

    public function enable(): bool
    {
        ConfigOptions::setValue('task_estimates_enabled', true, true);

        return true;
    }

    public function disable(): bool
    {
        ConfigOptions::setValue('task_estimates_enabled', false, true);

        return true;
    }

    public function isEnabled(): bool
    {
        return (bool) ConfigOptions::getValue('task_estimates_enabled');
    }

    public function isLocked(): bool
    {
        return (bool) ConfigOptions::getValue('task_estimates_enabled_lock');
    }
}
