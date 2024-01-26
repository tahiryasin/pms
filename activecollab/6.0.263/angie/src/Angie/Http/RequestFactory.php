<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Http;

use Angie\Inflector;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @package Angie\Http
 */
class RequestFactory extends ServerRequestFactory
{
    /**
     * Create request from arguments.
     *
     * @param  array   $server_params
     * @param  array   $uploaded_files
     * @param  null    $uri
     * @param  null    $method
     * @param  string  $body
     * @param  array   $headers
     * @param  array   $cookies
     * @param  array   $query_params
     * @param  null    $parsed_body
     * @param  string  $protocol
     * @return Request
     */
    public function create(array $server_params = [], array $uploaded_files = [], $uri = null, $method = null, $body = 'php://input', array $headers = [], array $cookies = [], array $query_params = [], $parsed_body = null, $protocol = '1.1')
    {
        return new Request($server_params, $uploaded_files, $uri, $method, $body, $headers, $cookies, $query_params, $parsed_body, $protocol);
    }

    /**
     * Construct request object from superglobals.
     *
     * @return Request
     */
    public function createFromGlobals()
    {
        $post = [];
        $server = $this->normalizeServer($_SERVER);
        $files = $this->normalizeFiles($_FILES);
        $headers = $this->marshalHeaders($server);
        $request_method = $this->get('REQUEST_METHOD', $server, 'GET');
        $content_type = $this->resolveContentType();

        if ($request_method === 'PUT' || $request_method === 'DELETE') {
            if (strpos($content_type, 'multipart/form-data') !== false) {
                // NOOP
            } elseif (strpos($content_type, 'application/json') !== false) {
                $input = $this->getPhpInput();

                if ($input) {
                    $post = json_decode($input, true);
                }
            } else {
                $input = $this->getPhpInput();

                if ($input) {
                    parse_str($input, $post);
                }
            }
        } elseif ($request_method === 'POST') {
            if (strpos($content_type, 'application/json') !== false) {
                $input = $this->getPhpInput();

                if ($input) {
                    $post = json_decode($input, true);
                }
            } elseif (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
                $post = $_POST;
            }
        }

        return new Request(
            $server,
            $files,
            $this->marshalUriFromServer($server, $headers),
            $request_method,
            'php://input',
            $headers,
            $_COOKIE,
            $_GET,
            $post
        );
    }

    /**
     * Override GET request params.
     *
     * @param  Request $request    current request object
     * @param  array   $url_params array of parameters extracted from URL
     * @return Request A new cloned copy of Request object
     */
    public function overrideQueryParams(Request $request, $url_params)
    {
        $get = [];
        $url_params = is_array($url_params) ? $url_params : [];

        foreach ($url_params as $key => $value) {
            if ($key !== 'controller' && $key !== 'action') {
                $get[$key] = $value;
            }
        }

        $module = isset($url_params['module']) && $url_params['module'] ? $url_params['module'] : DEFAULT_MODULE;
        $controller = isset($url_params['controller']) && $url_params['controller'] ? $url_params['controller'] : DEFAULT_CONTROLLER;
        if (isset($url_params['action']) && $url_params['action']) {
            $action = [];

            if (is_string($url_params['action'])) {
                $action['GET'] = $action['POST'] = $action['PUT'] = $action['DELETE'] = Inflector::underscore(trim($url_params['action'])); // @TODO Do we need this? It basically makes actions available for all request methods
            } elseif (is_array($url_params['action'])) {
                foreach ($url_params['action'] as $method => $controller_action) {
                    $action[strtoupper($method)] = Inflector::underscore($controller_action);
                }
            }
        } else {
            $action = ['GET' => 'index', 'POST' => 'index'];
        }

        /** @var Request $request */
        $request = $request->withQueryParams($url_params);
        $request->setRequestMetadata($module, $controller, $action);

        return $request;
    }

    /**
     * Resolve request content type.
     *
     * @return string
     */
    private function resolveContentType()
    {
        // Most servers that we tested use CONTENT_TYPE
        if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
            return $_SERVER['CONTENT_TYPE'];
        }

        // PHP built in server will send requests like this
        if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
            return $_SERVER['HTTP_CONTENT_TYPE'];
        }

        return '';
    }

    /**
     * Get input from STDIN.
     *
     * @return string
     */
    private function getPhpInput()
    {
        return trim(file_get_contents('php://input'));
    }
}
