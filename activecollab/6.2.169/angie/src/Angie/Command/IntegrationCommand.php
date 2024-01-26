<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Command;

abstract class IntegrationCommand extends Command
{
    protected function getCommandNamePrefix(): string
    {
        return 'integration:';
    }
}
