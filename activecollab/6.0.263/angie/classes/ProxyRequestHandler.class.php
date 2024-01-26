<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Base for all proxy.php request handlers.
 *
 * @package angie.library
 */
abstract class ProxyRequestHandler
{
    /**
     * Handle request based on provided data.
     */
    abstract public function execute();

    /**
     * Successfull response.
     */
    public function success()
    {
        header('HTTP/1.1 200 OK');
        die();
    }

    /**
     * Cached content is not modified.
     */
    public function notModified()
    {
        header('HTTP/1.1 304 Not Modified');
        die();
    }

    /**
     * Send not found HTTP header (404).
     */
    public function notFound()
    {
        header('HTTP/1.1 404 HTTP/1.1 404 Not Found');
        die('<h1>HTTP/1.1 404 Not Found</h1>');
    }

    /**
     * Send bad request HTTP header (400).
     */
    public function badRequest()
    {
        header('HTTP/1.1 400 HTTP/1.1 400 Bad Request');
        die('<h1>HTTP/1.1 400 Bad Request</h1>');
    }

    /**
     * Send bad request HTTP header (500).
     */
    public function operationFailed()
    {
        header('HTTP/1.1 500 HTTP/1.1 500 Internal Server Error');
        die('<h1>HTTP/1.1 500 Internal Server Error</h1>');
    }

    /**
     * Send unprocessable entity.
     */
    public function unprocessableEntity()
    {
        header('HTTP/1.1 422 HTTP/1.1 422 Unprocessable Entity');
        die('<h1>HTTP/1.1 422 Unprocessable Entity</h1>');
    }

    public function redirect($url, $moved_permanently = false)
    {
        header('Location: ' . $url, true, $moved_permanently ? 301 : 302);
        exit();
    }

    /**
     * Return cached ETag.
     *
     * @return string|null
     */
    protected function getCachedEtag()
    {
        return isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] ? $_SERVER['HTTP_IF_NONE_MATCH'] : null;
    }
}
