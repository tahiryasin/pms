<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Cache;

use Closure;
use InvalidParamError;
use Stash\Driver\Apc;
use Stash\Driver\FileSystem;
use Stash\Driver\Memcache;

interface CacheWrapperInterface
{
    /**
     * Return true if $key is cached.
     *
     * @param  mixed $key
     * @return bool
     */
    public function isCached($key);

    /**
     * Return value for a given key.
     *
     * @param  string|array $key
     * @param  mixed        $default
     * @param  bool         $force_refresh
     * @param  int|null     $lifetime
     * @return mixed|null
     */
    public function get($key, $default = null, $force_refresh = false, $lifetime = null);

    /**
     * Return by object.
     *
     * @param  object|array      $object
     * @param  string            $sub_namespace
     * @param  Closure|mixed     $default
     * @param  bool              $force_refresh
     * @param  int               $lifetime
     * @return mixed
     * @throws InvalidParamError
     */
    public function getByObject($object, $sub_namespace = null, $default = null, $force_refresh = false, $lifetime = null);

    /**
     * Return true if $object is instance that we can work with.
     *
     * @param  object $object
     * @return bool
     */
    public function isValidObject($object);

    /**
     * Cache given value.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @param  mixed $lifetime
     * @return mixed
     */
    public function set($key, $value, $lifetime = null);

    /**
     * Set value by given object.
     *
     * @param  object|array $object
     * @param  mixed        $sub_namespace
     * @param  mixed        $value
     * @param  int          $lifetime
     * @return mixed
     */
    public function setByObject($object, $sub_namespace, $value, $lifetime = null);

    /**
     * Remove value and all sub-nodes.
     *
     * @param $key
     */
    public function remove($key);

    /**
     * Remove data by given object.
     *
     * $sub_namespace let you additionally specify which part of object's cache should be removed, instead of entire
     * object cache. Example:
     *
     * AngieApplication::cache()->removeByObject($user, 'permissions_cache');
     *
     * @param       $object
     * @param mixed $sub_namespace
     */
    public function removeByObject($object, $sub_namespace = null);

    /**
     * Remove model name.
     *
     * @param string $model_name
     */
    public function removeByModel($model_name);

    /**
     * Clear entire cache.
     */
    public function clear();

    /**
     * Clear model cache.
     */
    public function clearModelCache();

    /**
     * Return cache key for given object.
     *
     * This function receives either an object instance, or array where first element is model name and second is
     * object ID
     *
     * Optional $sub_namespace can be used to additionally dig into object's cache. String value and array of string
     * values are accepted
     *
     * @param  object            $object
     * @param  mixed             $subnamespace
     * @return array
     * @throws InvalidParamError
     */
    public function getCacheKeyForObject($object, $subnamespace = null);

    /**
     * Initialize memcached backend.
     *
     * @return Memcache
     */
    public function getMemcacheDriver();

    /**
     * Parse memcached servers list.
     *
     * Note: This method is public so we can test it
     *
     * @param  string $list
     * @return array
     */
    public function parseMemcachedServersList($list);

    /**
     * Initialize APC cache backend.
     *
     * @param  string $namespace
     * @return Apc
     */
    public function getApcDriver($namespace = null);

    /**
     * Initialize file system based cache backend.
     *
     * @param  string     $path
     * @return FileSystem
     */
    public function getFileSystemDriver($path);

    /**
     * Return backend type.
     *
     * @return string
     */
    public function getBackendType();

    /**
     * Return lifetime.
     *
     * @param $lifetime
     * @return mixed
     */
    public function getLifetime($lifetime = null);

    /**
     * Set default cache lifetime.
     *
     * @param $value
     */
    public function setLifetime($value);
}
