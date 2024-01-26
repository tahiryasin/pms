<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Route public request to appropriate handler.
 */
if (isset($this) && $this instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
    return;
}

// Make sure that request is routed through /instance/proxy.php
if (!(defined('PROXY_HANDLER_REQUEST') && PROXY_HANDLER_REQUEST)) {
    header('HTTP/1.0 404 Not Found');
}

$proxy_name = null;
if (isset($_GET['proxy'])) {
    $proxy_name = $_GET['proxy'] ? trim($_GET['proxy']) : null;
    unset($_GET['proxy']);
}

$module = null;
if (isset($_GET['module'])) {
    $module = $_GET['module'] ? trim($_GET['module']) : null;
    unset($_GET['module']);
}

// Validate input
if (($proxy_name && preg_match('/\W/', $proxy_name) == 0) && ($module && preg_match('/\W/', $module) == 0)) {
    $proxy_class = str_replace(' ', '', ucwords(str_replace('_', ' ', $proxy_name))) . 'Proxy';

    require_once ANGIE_PATH . '/classes/ProxyRequestHandler.class.php';

    $possible_paths = [
        APPLICATION_PATH . "/modules/$module/proxies/$proxy_class.class.php",
        ANGIE_PATH . "/frameworks/$module/proxies/$proxy_class.class.php",
    ];

    foreach ($possible_paths as $possible_path) {
        if (is_file($possible_path)) {
            require_once $possible_path;

            if (class_exists($proxy_class)) {
                $proxy = new $proxy_class($_GET);
                if ($proxy instanceof ProxyRequestHandler) {
                    $proxy->execute();
                    die();
                }
            }
        }
    }
}

// Handler not found
header('HTTP/1.0 404 Not Found');
