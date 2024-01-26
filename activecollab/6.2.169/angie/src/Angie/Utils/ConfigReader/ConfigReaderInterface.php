<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace Angie\Utils\ConfigReader;

interface ConfigReaderInterface
{
    /**
     * @param  string $option_name
     * @return mixed
     */
    public function getValue(string $option_name);
}
