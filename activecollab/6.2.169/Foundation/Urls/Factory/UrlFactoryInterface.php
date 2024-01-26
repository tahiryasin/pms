<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Urls\Factory;

use ActiveCollab\Foundation\Urls\UrlInterface;

interface UrlFactoryInterface
{
    public function createFromUrl(string $url): UrlInterface;
}
