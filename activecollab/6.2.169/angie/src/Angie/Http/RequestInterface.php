<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @package Angie\Http
 */
interface RequestInterface extends ServerRequestInterface
{
    /**
     * Set required metadata for further request dispatch.
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $action
     * @return $this
     */
    public function setRequestMetadata($module, $controller, $action);

    /**
     * Return name of the module that needs to serve this request.
     *
     * @return string
     */
    public function getModule();

    /**
     * Return requested controller.
     *
     * @return string
     */
    public function getController();

    /**
     * Return requested action.
     *
     * @return string
     */
    public function getAction();

    /**
     * Return action name for the given method.
     *
     * @param  string $method
     * @return string
     */
    public function getActionForMethod($method);

    /**
     * Return variable from GET.
     *
     * If $var is NULL, entire GET array will be returned
     *
     * @param  string $var
     * @param  mixed  $default
     * @return mixed
     */
    public function get($var = null, $default = null);

    /**
     * Return ID.
     *
     * This function will extract ID value from request. If $from is NULL get
     * will be used, else it will be extracted from $from. Default value is
     * returned if ID is missing
     *
     * @param  string $name
     * @param  array  $from
     * @param  mixed  $default
     * @return int
     */
    public function getId($name = 'id', $from = null, $default = null);

    /**
     * Return page number.
     *
     * @param  string $variable_name
     * @return int
     */
    public function getPage($variable_name = 'page');

    /**
     * Return POST variable.
     *
     * If $var is NULL, entire POST array will be returned
     *
     * @param  string      $var
     * @param  mixed       $default
     * @return array|mixed
     */
    public function post($var = null, $default = null);

    /**
     * Return PUT value.
     *
     * @param  string $var
     * @param  mixed  $default
     * @return mixed
     */
    public function put($var = null, $default = null);

    /**
     * @param  string      $key
     * @return string|null
     */
    public function getServerParam($key);

    /**
     * @return string
     */
    public function getQueryString();
}
