<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\App\RootUrl;

class RootUrl implements RootUrlInterface
{
    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isInternalUrl(string $url_to_check): bool
    {
        return str_starts_with($url_to_check, $this->url);
    }
}
