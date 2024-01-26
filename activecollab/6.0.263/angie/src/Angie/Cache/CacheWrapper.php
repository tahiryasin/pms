<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Cache;

use Angie\Inflector;
use AngieApplication;
use Closure;
use DataObject;
use InvalidParamError;
use RuntimeException;
use Stash\Driver\Apc;
use Stash\Driver\FileSystem;
use Stash\Driver\FileSystem\SerializerEncoder;
use Stash\Driver\Memcache;
use Stash\Interfaces\ItemInterface;
use Stash\Pool;

/**
 * Angie cache delegate implementation.
 *
 * @package angie.library.application
 * @subpackage delegates
 */
class CacheWrapper implements CacheWrapperInterface
{
    const FILESYSTEM_BACKEND = 'filesystem';
    const MEMCACHED_BACKEND = 'memcached';
    const APC_BACKEND = 'apc';

    /**
     * Return true if $key is cached.
     *
     * @param  mixed $key
     * @return bool
     */
    public function isCached($key)
    {
        $stash = $this->getStash($this->getKey($key));
        $stash->get();

        return !$stash->isMiss();
    }

    /**
     * Return value for a given key.
     *
     * @param  string|array $key
     * @param  mixed        $default
     * @param  bool         $force_refresh
     * @param  int|null     $lifetime
     * @return mixed|null
     */
    public function get($key, $default = null, $force_refresh = false, $lifetime = null)
    {
        $stash = $this->getStash($this->getKey($key));

        $data = $stash->get();

        if ($force_refresh || $stash->isMiss()) {
            $data = $default instanceof Closure ? $default->__invoke() : $default;
            $stash->set($data, $this->getLifetime($lifetime));
        }

        return $data;
    }

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
    public function getByObject($object, $sub_namespace = null, $default = null, $force_refresh = false, $lifetime = null)
    {
        if ($this->isValidObject($object)) {
            return $this->get($this->getCacheKeyForObject($object, $sub_namespace), $default, $force_refresh, $lifetime);
        } else {
            throw new InvalidParamError('object', $object, '$object is not a valid cache context');
        }
    }

    /**
     * Return true if $object is instance that we can work with.
     *
     * @param  object $object
     * @return bool
     */
    public function isValidObject($object)
    {
        if ($object instanceof DataObject) {
            return $object->isLoaded();
        } elseif (is_array($object) && count($object) == 2) {
            return true;
        } else {
            return is_object($object) && method_exists($object, 'getId') && method_exists($object, 'getModelName') && $object->getId();
        }
    }

    /**
     * Cache given value.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @param  mixed $lifetime
     * @return mixed
     */
    public function set($key, $value, $lifetime = null)
    {
        $this->getStash($this->getKey($key))->set($value, $this->getLifetime($lifetime));

        return $value;
    }

    /**
     * Set value by given object.
     *
     * @param  object|array $object
     * @param  mixed        $sub_namespace
     * @param  mixed        $value
     * @param  int          $lifetime
     * @return mixed
     */
    public function setByObject($object, $sub_namespace, $value, $lifetime = null)
    {
        if ($this->isValidObject($object)) {
            return $this->set($this->getCacheKeyForObject($object, $sub_namespace), $value, $lifetime);
        } else {
            return false; // Not supported for objects that are not persisted
        }
    }

    /**
     * Remove value and all sub-nodes.
     *
     * @param $key
     */
    public function remove($key)
    {
        $this->getStash($key)->clear();
    }

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
    public function removeByObject($object, $sub_namespace = null)
    {
        $this->remove($this->getCacheKeyForObject($object, $sub_namespace));
    }

    /**
     * Remove model name.
     *
     * @param string $model_name
     */
    public function removeByModel($model_name)
    {
        $this->remove(['models', $model_name]);
    }

    /**
     * Clear entire cache.
     */
    public function clear()
    {
        if ($this->getPool()->getDriver() instanceof FileSystem) {
            empty_dir(CACHE_LIFETIME, true);
        } else {
            $this->getPool()->flush();
        }
    }

    /**
     * Clear model cache.
     */
    public function clearModelCache()
    {
        $this->remove('models');
    }

    // ---------------------------------------------------
    //  Data Object Related
    // ---------------------------------------------------

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
    public function getCacheKeyForObject($object, $subnamespace = null)
    {
        // Data object
        if ($object instanceof DataObject) {
            return get_data_object_cache_key($object->getModelName(true), $object->getId(), $subnamespace);

            // Data object as array
        } elseif (is_array($object) && count($object) == 2) {
            [$model_name, $object_id] = $object;

            return get_data_object_cache_key($model_name, $object_id, $subnamespace);

            // Class that has getId() method
        } elseif (is_object($object) && method_exists($object, 'getId')) {
            return get_data_object_cache_key(Inflector::pluralize(Inflector::underscore(get_class($object))), $object->getId(), $subnamespace);

            // Invalid object
        } else {
            throw new InvalidParamError('object', $object, '$object is expected to be loaded object instance with getId method defined or an array that has model name and object ID');
        }
    }

    // ---------------------------------------------------
    //  Internal, Stash Related Functions
    // ---------------------------------------------------

    /**
     * Cache pool instance.
     *
     * @var Pool
     */
    private $pool;

    /**
     * Return cache pool.
     *
     * @return Pool
     */
    private function &getPool()
    {
        if (empty($this->pool)) {
            $backend = self::FILESYSTEM_BACKEND; // Default cache backend

            if (defined('CACHE_BACKEND') && CACHE_BACKEND) {
                switch (CACHE_BACKEND) {
                    case 'MemcachedCacheBackend':
                    case self::MEMCACHED_BACKEND:
                        $backend = self::MEMCACHED_BACKEND;
                        break;

                    case 'APCCacheBackend':
                    case self::APC_BACKEND:
                        $backend = self::APC_BACKEND;
                        break;
                }
            }

            $this->pool = new Pool();

            switch ($backend) {
                case self::MEMCACHED_BACKEND:
                    $this->pool->setDriver($this->getMemcacheDriver());
                    break;
                case self::APC_BACKEND:
                    $this->pool->setDriver($this->getApcDriver());
                    break;
                default:
                    if (!self::allowFileSystemCache()) {
                        throw new RuntimeException('On Demand system cannot use file system cache. Check configuration');
                    }

                    $this->pool->setDriver($this->getFileSystemDriver(CACHE_PATH));
            }
        }

        return $this->pool;
    }

    private function allowFileSystemCache()
    {
        return AngieApplication::isInTestMode()
            || AngieApplication::isInDevelopment()
            || !AngieApplication::isOnDemand();
    }

    /**
     * Return stash instance.
     *
     * @param  string|array  $key
     * @return ItemInterface
     */
    private function getStash($key)
    {
        return $this->getPool()->getItem($key);
    }

    /**
     * Initialize memcached backend.
     *
     * @return Memcache
     */
    public function getMemcacheDriver()
    {
        defined('CACHE_MEMCACHED_SERVERS') or define('CACHE_MEMCACHED_SERVERS', '');

        $prefix_key = defined('CACHE_MEMCACHED_PREFIX') && CACHE_MEMCACHED_PREFIX
            ? CACHE_MEMCACHED_PREFIX
            : AngieApplication::getAccountId() . '-' . APPLICATION_UNIQUE_KEY;

        $driver = new Memcache();
        $driver->setOptions([
            'servers' => $this->parseMemcachedServersList(CACHE_MEMCACHED_SERVERS), // Return array of memcached servers
            'prefix_key' => $prefix_key,
        ]);

        return $driver;
    }

    /**
     * Parse memcached servers list.
     *
     * Note: This method is public so we can test it
     *
     * @param  string $list
     * @return array
     */
    public function parseMemcachedServersList($list)
    {
        $result = [];

        if ($list) {
            foreach (explode(',', $list) as $server) {
                if (strpos($server, '/') !== false) {
                    [$server_url, $weight] = explode('/', $server);
                } else {
                    $server_url = $server;
                    $weight = 1;
                }

                $parts = parse_url($server_url);

                if (empty($parts['host'])) {
                    if (empty($parts['path'])) {
                        continue; // Ignore
                    } else {
                        $host = $parts['path'];
                    }
                } else {
                    $host = $parts['host'];
                }

                $result[] = [$host, array_var($parts, 'port', '11211'), $weight];
            }
        }

        return $result;
    }

    /**
     * Initialize APC cache backend.
     *
     * @param  string $namespace
     * @return Apc
     */
    public function getApcDriver($namespace = null)
    {
        $driver = new Apc();
        $driver->setOptions([
            'ttl' => $this->getLifetime(),
            'namespace' => empty($namespace) ? md5(APPLICATION_UNIQUE_KEY) : $namespace,
        ]);

        return $driver;
    }

    /**
     * Initialize file system based cache backend.
     *
     * @param  string     $path
     * @return FileSystem
     */
    public function getFileSystemDriver($path)
    {
        $driver = new FileSystem();
        $driver->setOptions([
            'dirSplit' => 1,
            'path' => $path,
            'filePermissions' => 0777,
            'dirPermissions' => 0777,
            'encoder' => new SerializerEncoder(),
        ]);

        return $driver;
    }

    /**
     * Return backend type.
     *
     * @return string
     */
    public function getBackendType()
    {
        if ($this->pool) {
            if ($this->pool->getDriver() instanceof Memcache) {
                return self::MEMCACHED_BACKEND;
            } elseif ($this->pool->getDriver() instanceof Apc) {
                return self::APC_BACKEND;
            } elseif ($this->pool->getDriver() instanceof FileSystem) {
                return self::FILESYSTEM_BACKEND;
            }
        }

        return null;
    }

    // ---------------------------------------------------
    //  Input Converters
    // ---------------------------------------------------

    /**
     * Prepare and return key that Stash understands.
     *
     * @param  string|array $key
     * @return array
     */
    private function getKey($key)
    {
        return $key;
    }

    /**
     * Default cache lifetime.
     *
     * @var int
     */
    private $lifetime = 3600;

    /**
     * Return lifetime.
     *
     * @param $lifetime
     * @return mixed
     */
    public function getLifetime($lifetime = null)
    {
        return $lifetime ? $lifetime : $this->lifetime;
    }

    /**
     * Set default cache lifetime.
     *
     * @param $value
     */
    public function setLifetime($value)
    {
        $this->lifetime = (int) $value;
    }
}
