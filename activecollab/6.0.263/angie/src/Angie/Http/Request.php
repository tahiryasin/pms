<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Http;

use Zend\Diactoros\ServerRequest;

/**
 * @package Angie\Http
 */
class Request extends ServerRequest implements RequestInterface
{
    /**
     * Cached module.
     *
     * @var string
     */
    private $module;

    /**
     * Cached controller.
     *
     * @var string
     */
    private $controller;

    /**
     * Cached action.
     *
     * @var string|array
     */
    private $action;

    /**
     * @var string
     */
    private $query_string;

    /**
     * {@inheritdoc}
     */
    public function setRequestMetadata($module, $controller, $action)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return first($this->action);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionForMethod($method)
    {
        return isset($this->action[$method]) ? $this->action[$method] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function get($var = null, $default = null)
    {
        $get = $this->getQueryParams();

        if (!$var) {
            return $get;
        }

        switch ($var) {
            case 'module':
                return $this->module;
            case 'controller':
                return $this->controller;
            case 'action':
                return $this->action;
            default:
                return isset($get[$var]) ? $get[$var] : $default;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId($name = 'id', $from = null, $default = null)
    {
        return $from === null
            ? (int) $this->get($name, $default)
            : (int) array_var($from, $name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getPage($variable_name = 'page')
    {
        $page = (int) $this->get($variable_name);

        return $page < 1 ? 1 : $page;
    }

    /**
     * {@inheritdoc}
     */
    public function post($var = null, $default = null)
    {
        $post = $this->getParsedBody();

        if (empty($post)) {
            $post = [];
        }

        if (!$var) {
            return $post;
        }

        return isset($post[$var]) ? $post[$var] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put($var = null, $default = null)
    {
        return $this->post($var, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParam($key)
    {
        return $this->getParam($key, $this->getServerParams());
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryString()
    {
        if ($this->query_string) {
            return $this->query_string;
        }

        $query_string = $this->getServerParam('QUERY_STRING');
        $request_uri = $this->getServerParam('REQUEST_URI');

        if ($query_string) {
            $this->query_string = $query_string;
        } elseif (($pos = strpos($request_uri, '?')) !== false) {
            $this->query_string = substr($request_uri, $pos + 1);
        }

        if ($this->query_string) {
            $parsed_query_string = [];
            parse_str($this->query_string, $parsed_query_string);

            foreach (['path_info', 'api_version', 'HTTP_X_ANGIE_CSRFVALIDATOR'] as $key) {
                if (isset($parsed_query_string[$key])) {
                    unset($parsed_query_string[$key]);
                }
            }

            $this->query_string = http_build_query($parsed_query_string);
        }

        return $this->query_string;
    }

    /**
     * {@inheritdoc}
     */
    private function getParam($key, array $values)
    {
        return isset($values[$key]) ? $values[$key] : null;
    }
}
