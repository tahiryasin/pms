<?php

/*
 * This file is part of the Active Collab Cookies project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Cookies\Adapter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface AdapterInterface.
 *
 * @package ActiveCollab\Cookies
 */
interface AdapterInterface
{
    /**
     * Return true if cookie with the given name exists.
     *
     * @param  ServerRequestInterface $request
     * @param  string                 $name
     * @return bool
     */
    public function exists(ServerRequestInterface $request, $name);

    /**
     * Return a cookie value. If cookie is not found, $default is returned.
     *
     * Available settings:
     *
     * - decrypt
     *
     * @param  ServerRequestInterface $request
     * @param  string                 $name
     * @param  mixed                  $default
     * @param  array                  $settings
     * @return mixed
     */
    public function get(ServerRequestInterface $request, $name, $default = null, array $settings = []);

    /**
     * Set or modify a cookie value.
     *
     * Available settings:
     *
     * - domain
     * - path
     * - ttl
     * - secure
     * - http_only
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  string                 $name
     * @param  string                 $value
     * @param  array                  $settings
     * @return array
     */
    public function set(ServerRequestInterface $request, ResponseInterface $response, $name, $value, array $settings = []);

    /**
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  string                 $name
     * @return array
     */
    public function remove(ServerRequestInterface $request, ResponseInterface $response, $name);
}
