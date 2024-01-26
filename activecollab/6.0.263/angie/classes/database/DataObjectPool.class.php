<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEventInterface;
use Angie\Events;
use Angie\Inflector;

/**
 * Data object pool.
 *
 * Static class that's used to cache object instancess acorss the application
 *
 * @package angie.library.database
 */
final class DataObjectPool
{
    const OBJECT_CREATED = 'created';
    const OBJECT_UPDATED = 'updated';
    const OBJECT_ARCHIVED = 'archived';
    const OBJECT_UNARCHIVED = 'unarchived';
    const OBJECT_DELETED = 'deleted';

    /**
     * Cache all objects in this variable, indexed by type and ID.
     *
     * @var array
     */
    public static $pool = [];
    /**
     * Registered type loaders.
     *
     * @var array
     */
    public static $type_loaders = [];

    /**
     * Return object by type -> id pair, by forcefully reload it.
     *
     * @param  string     $type
     * @param  int        $id
     * @param  Closure    $alternative
     * @return DataObject
     */
    public static function &reload($type, $id, $alternative = null)
    {
        return self::get($type, $id, $alternative, true);
    }

    /**
     * Return object by type -> id pair.
     *
     * @param  string     $type
     * @param  int        $id
     * @param  Closure    $alternative
     * @param  bool       $force_reload
     * @return DataObject
     */
    public static function &get($type, $id, $alternative = null, $force_reload = false)
    {
        if ($id) {
            if (isset(self::$pool[$type]) && isset(self::$pool[$type][$id]) && empty($force_reload)) {
                return self::$pool[$type][$id];
            } else {
                $object = self::loadById($type, $id);

                if ($object instanceof DataObject && $object->isLoaded()) {
                    self::$pool[$type][$id] = $object;
                } else {
                    self::$pool[$type][$id] = null;
                }

                return self::$pool[$type][$id];
            }
        }

        if ($alternative instanceof Closure) {
            $result = call_user_func($alternative);
        } else {
            $result = $alternative;
        }

        return $result;
    }

    /**
     * Load first object by ID.
     *
     * @param  string     $type
     * @param  int        $id
     * @return DataObject
     */
    private static function loadById($type, $id)
    {
        $type_loader = self::getTypeLoader($type);

        if ($type_loader) {
            $loader_result = $type_loader([$id]);

            if ($loader_result) {
                foreach ($loader_result as $v) {
                    return $v;
                }
            }
        }

        return null;
    }

    /**
     * Return type loader.
     *
     * @param  string       $type
     * @return Closure|null
     */
    public static function getTypeLoader($type)
    {
        return isset(self::$type_loaders[$type]) && self::$type_loaders[$type] instanceof Closure ? self::$type_loaders[$type] : null;
    }

    /**
     * Add object to the pool.
     *
     * @param DataObject $object
     */
    public static function introduce(DataObject &$object)
    {
        self::$pool[get_class($object)][$object->getId()] = $object;
    }

    /**
     * Announce that $object changed its state to $new_lifecycle_state.
     *
     * @param  DataObject|DataObjectLifeCycleEventInterface $object
     * @param  string                                       $new_lifecycle_state
     * @param  array                                        $attributes
     * @return DataObject
     */
    public static function &announce(
        $object,
        $new_lifecycle_state = self::OBJECT_CREATED,
        array $attributes = null
    )
    {
        if ($object instanceof DataObject) {
            if ($object->isLoaded()) {
                Events::trigger(
                    self::getAnnouncementEventName($object, $new_lifecycle_state),
                    [
                        &$object,
                        $attributes,
                    ]
                );
            }

            return $object;
        } elseif ($object instanceof DataObjectLifeCycleEventInterface) {
            AngieApplication::eventsDispatcher()->trigger($object);

            $result = $object->getObject();

            return $result;
        } else {
            throw new LogicException('Only data object and data object lifecycle events are accepted.');
        }
    }

    private static function getAnnouncementEventName(DataObject $object, $new_lifecycle_state): string
    {
        return 'on_' . Inflector::underscore(get_class($object)) . '_' . $new_lifecycle_state;
    }

    /**
     * Remove object from the pool. If $id_or_ids is NULL, all entries for the given type will be forgotten.
     *
     * @param string    $type
     * @param int|int[] $id_or_ids
     */
    public static function forget($type, $id_or_ids = null)
    {
        if (empty(self::$pool[$type])) {
            return;
        }

        if ($id_or_ids) {
            foreach ((array) $id_or_ids as $id) {
                if (!empty(self::$pool[$type][$id])) {
                    unset(self::$pool[$type][$id]);
                }
            }
        } elseif ($id_or_ids === null) {
            unset(self::$pool[$type]);
        }
    }

    /**
     * Return objects by type -> ids map.
     *
     * @param  array $map
     * @return array
     */
    public static function getByTypeIdsMap($map)
    {
        if ($map && is_foreachable($map)) {
            $result = [];

            foreach ($map as $type => $ids) {
                $result[$type] = self::getByIds($type, $ids);

                if (empty($result[$type])) {
                    unset($result[$type]);
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * Return instances by $type and $ids.
     *
     * @param  string            $type
     * @param  int[]             $ids
     * @return DataObject[]|null
     */
    public static function getByIds($type, $ids)
    {
        if (empty($ids)) {
            return null;
        }

        $type_loader = self::getTypeLoader($type); // isset(self::$type_loaders[$type]) && self::$type_loaders[$type] instanceof Closure ? self::$type_loaders[$type] : null;

        if ($type_loader) {
            /** @var DataObject[] $loader_result */
            $loader_result = $type_loader(array_unique($ids));

            if ($loader_result) {
                $objects = [];

                foreach ($loader_result as $v) {
                    $objects[$v->getId()] = $v;

                    if (empty(self::$pool[$type])) {
                        self::$pool[$type] = [];
                    }

                    self::$pool[$type][$v->getId()] = $v;
                }
            } else {
                $objects = null;
            }
        } else {
            $objects = [];

            foreach ($ids as $id) {
                $object = self::get($type, $id);

                if ($object) {
                    $objects[$id] = $object;
                }
            }
        }

        return empty($objects) ? null : $objects;
    }

    /**
     * Register type loader.
     *
     * $type can be a signle type or an array of types
     *
     * $callback can be a closure or callback array
     *
     * @param array|string $type
     * @param mixed        $callback
     */
    public static function registerTypeLoader($type, $callback)
    {
        if (is_array($type)) {
            foreach ($type as $v) {
                self::$type_loaders[$v] = $callback;
            }
        } else {
            self::$type_loaders[$type] = $callback;
        }
    }

    /**
     * Clear data object pool.
     */
    public static function clear()
    {
        self::$pool = [];
    }
}
