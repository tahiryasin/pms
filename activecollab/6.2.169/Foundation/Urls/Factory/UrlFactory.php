<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Foundation\Urls\Factory;

use ActiveCollab\Foundation\App\RootUrl\RootUrlInterface;
use ActiveCollab\Foundation\Urls\ExternalUrl;
use ActiveCollab\Foundation\Urls\InternalUrl;
use ActiveCollab\Foundation\Urls\ModalArguments\ModalArguments;
use ActiveCollab\Foundation\Urls\ModalArguments\ModalArgumentsInterface;
use ActiveCollab\Foundation\Urls\Router\UrlAssembler\UrlAssemblerInterface;
use ActiveCollab\Foundation\Urls\UrlInterface;
use InvalidArgumentException;

class UrlFactory implements UrlFactoryInterface
{
    private $url_assembler;
    private $root_url;

    public function __construct(UrlAssemblerInterface $url_assembler, RootUrlInterface $root_url)
    {
        $this->url_assembler = $url_assembler;
        $this->root_url = $root_url;
    }

    public function createFromUrl(string $url): UrlInterface
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid URL', $url));
        }

        if ($this->root_url->isInternalUrl($url)) {
            return new InternalUrl($url, $this->getModalArguments($url));
        } else {
            return new ExternalUrl($url);
        }
    }

    private function getModalArguments(string $url): ?ModalArgumentsInterface
    {
        $parsed_url_bits = parse_url($url);

        if (!empty($parsed_url_bits['query'])) {
            $query = [];

            parse_str(
                str_replace(
                    '&amp;',
                    '&',
                    $parsed_url_bits['query']
                ),
                $query
            );

            if (!empty($query['modal'])) {
                $modal_bits = explode('-', $query['modal']);
                $modal_bits_count = count($modal_bits);

                if ($modal_bits_count >= 3) {
                    for ($i = 1; $i < count($modal_bits); $i++) {
                        if (!ctype_digit($modal_bits[$i])) {
                            return null;
                        }
                    }

                    return new ModalArguments(
                        $this->url_assembler,
                        $modal_bits[0],
                        (int) $modal_bits[1],
                        (int) $modal_bits[2],
                        !empty($modal_bits[3]) ? (int) $modal_bits[3] : null
                    );
                } else {
                    return null;
                }
            }
        }

        return null;
    }
}
