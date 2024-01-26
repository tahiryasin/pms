<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */
ini_set('display_errors','1');

/**
 * Public interface file.
 *
 * @package ActiveCollab
 */

// Check minimal PHP version before we hit syntax errors in framework files
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    print '<h1>Active Collab Error</h1>';
    print '<p>Active Collab requires PHP 5.6 or newer, but you appear to have PHP ' . (version_compare(PHP_VERSION, '5.2.7', '>=') ? PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION : PHP_VERSION) . '. Please upgrade your PHP.</p>';
    print '<p style="text-align: center; margin-top: 50px;">&copy; 2007-' . date('Y') . ' <a href="https://www.activecollab.com">Active Collab</a> &mdash; powerful, yet simple project and task management.</p>';

    die();
}

define('ANGIE_SCRIPT_TIME', microtime(true));
define('PUBLIC_PATH', DIRECTORY_SEPARATOR == '\\' ? str_replace('\\', '/', __DIR__) : __DIR__);
define('CONFIG_PATH', dirname(PUBLIC_PATH) . '/config');

if (file_exists(CONFIG_PATH . '/config.php')) {
    require_once CONFIG_PATH . '/config.php';

    defined('FRONTEND_PATH') or define('FRONTEND_PATH', APPLICATION_PATH . '/frontend');

    require_once FRONTEND_PATH . '/frontend.php';
} else {
    require 'install.php';
}
