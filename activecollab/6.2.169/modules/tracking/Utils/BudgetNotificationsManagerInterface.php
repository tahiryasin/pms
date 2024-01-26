<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Utils;

interface BudgetNotificationsManagerInterface
{
    public function getProjectsIds(): array;
    public function findProjectsThatReachedThreshold(): array;
}
