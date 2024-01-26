<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie;

/**
 * Cookie service class.
 *
 * Cookie service maps $_COOKIE and prvides simple methods for setting and
 * getting cookie data. If adds prefixes to variable name to provide just to
 * make sure that we are using correct data
 *
 * @package angie.library
 */
final class Cookies
{
    /**
     * @var string
     */
    private static $path = '/';
    private static $prefix = 'ac';
    private static $domain;

    /**
     * @var int
     */
    private static $secure = 0;

    /**
     * Default cookie expiration time (14 days).
     *
     * @var int
     */
    private static $expiration_time = 1209600;

    /**
     * Init cookie service.
     */
    public static function init()
    {
        self::$domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : self::cookieDomainFromUrl(ROOT_URL);
        self::$secure = self::cookieSecureFromUrl(ROOT_URL);
    }

    /**
     * Return variable value from cookie.
     *
     * @param  string $name Variable name
     * @return mixed
     */
    public static function getVariable($name)
    {
        $var_name = self::getVariableName($name);

        return isset($_COOKIE[$var_name]) ? $_COOKIE[$var_name] : null;
    }

    /**
     * Set cookie variable.
     *
     * @param  string $name      Variable name, without prefix
     * @param  mixed  $value     Value that need to be set
     * @param  int    $exp_time  Expiration time, in seconds
     * @param  bool   $http_only
     * @return bool
     */
    public static function setVariable($name, $value, $exp_time = null, $http_only = true)
    {
        return (bool) setcookie(self::getVariableName($name), $value, self::getExpirationTime($exp_time), self::$path, self::$domain, self::$secure, $http_only);
    }

    /**
     * Unset cookie variable.
     *
     * @param string $name Cookie name
     */
    public static function unsetVariable($name)
    {
        $var_name = self::getVariableName($name);

        self::setVariable($name, null);
        $_COOKIE[$var_name] = null;
    }

    /**
     * Put prefix in front of variable name if available.
     *
     * @param  string $name Original name
     * @return string
     */
    public static function getVariableName($name)
    {
        $prefix = self::$prefix;

        if ($prefix) {
            $prefix .= '_';
        }

        return "$prefix$name";
    }

    /**
     * Return expiration time based on the input value.
     *
     * @param  int|null $exp_time
     * @return int
     */
    private static function getExpirationTime($exp_time)
    {
        $exp_time = (int) $exp_time;

        if ($exp_time < 1) {
            $exp_time = self::$expiration_time;
        }

        return time() + $exp_time;
    }

    /**
     * Get cookie domain from URL.
     *
     * @param  string $url
     * @return string
     */
    public static function cookieDomainFromUrl($url)
    {
        $parts = parse_url($url);

        return is_array($parts) && isset($parts['host']) ? $parts['host'] : '';
    }

    /**
     * Should cookie be secure or not.
     *
     * @param  string $url
     * @return bool
     */
    public static function cookieSecureFromUrl($url)
    {
        return substr($url, 0, 5) == 'https' ? 1 : 0;
    }
}

Cookies::init();
