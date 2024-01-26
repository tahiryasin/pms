<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Error;

/**
 * Data manager class.
 *
 * This class provides interface for extracting multiple rows form a specific
 * table and population of item objects with extracted data
 *
 * @package angie.library.database
 */
abstract class DataManager
{
    const ALL = 'all';
    const ACTIVE = 'active';
    const ARCHIVED = 'archived';

    /**
     * How do we know which class name to use.
     *
     * - CLASS_NAME_FROM_TABLE - Class name from table name, value is prepared
     *   by generator
     * - CLASS_NAME_FROM_FIELD - Load class name from row field
     */
    const CLASS_NAME_FROM_TABLE = 0;
    const CLASS_NAME_FROM_FIELD = 1;

    /**
     * Cached trait names by class.
     *
     * @var array
     */
    protected static $traits_by_object = [];

    /**
     * Clear model cache.
     */
    public static function clearCache()
    {
        AngieApplication::cache()->removeByModel(static::getModelName(true));
        DataObjectPool::forget(static::getInstanceClassName());
    }

    /**
     * Return name of this model.
     *
     * @param  bool                $underscore
     * @return string
     * @throws NotImplementedError
     */
    public static function getModelName($underscore = false)
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Return class name of a single instance.
     *
     * @return string
     * @throws NotImplementedError
     */
    public static function getInstanceClassName()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Clear cache for a particular object.
     *
     * @param array|int $object_ids
     */
    public static function clearCacheFor($object_ids)
    {
        $object_ids = $object_ids ? (array) $object_ids : [];

        foreach ($object_ids as $object_id) {
            AngieApplication::cache()->remove(get_data_object_cache_key(static::getModelName(true), $object_id));
        }

        DataObjectPool::forget(static::getInstanceClassName(), $object_ids);
    }

    /**
     * Parent to conditions.
     *
     * @param  DataObject|IActivityLog $parent
     * @param  bool                    $include_state_check
     * @return string
     * @throws InvalidInstanceError
     */
    public static function parentToCondition($parent, $include_state_check = false)
    {
        $table_name = static::getTableName();

        if ($parent instanceof ApplicationObject) {
            if ($include_state_check && $parent instanceof ITrash && !$parent->getIsTrashed()) {
                $state_check = DB::prepare("$table_name.is_trashed = ?", false);
            }

            if (isset($state_check)) {
                return DB::prepare("($table_name.parent_type = ? AND $table_name.parent_id = ? AND $state_check)", get_class($parent), $parent->getId());
            } else {
                return DB::prepare("($table_name.parent_type = ? AND $table_name.parent_id = ?)", get_class($parent), $parent->getId());
            }
        } else {
            throw new InvalidInstanceError('parent', $parent, 'DataObject');
        }
    }

    /**
     * Return name of the table where system will persist model instances.
     *
     * @return string
     * @throws NotImplementedError
     */
    public static function getTableName()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Parent not set condition.
     *
     * @return string
     */
    public static function parentNotSetCondition()
    {
        $table_name = static::getTableName();

        return DB::prepare("($table_name.parent_type IS NULL AND ($table_name.parent_id IS NULL OR $table_name.parent_id = ''))");
    }

    /**
     * Check model Etag.
     *
     * @param  int    $id
     * @param  string $hash
     * @return bool
     */
    public static function checkObjectEtag($id, $hash)
    {
        if (static::fieldExists('updated_on')) {
            if ($updated_on = DB::executeFirstCell('SELECT updated_on FROM ' . static::getTableName() . ' WHERE id = ?', $id)) {
                return $hash == sha1(APPLICATION_UNIQUE_KEY . $updated_on);
            }
        }

        return false;
    }

    /**
     * Return true if $field_name exists in this model.
     *
     * @param  string $field_name
     * @return bool
     */
    public static function fieldExists($field_name)
    {
        return in_array($field_name, static::getFields());
    }

    /**
     * Return all model fields.
     *
     * @return array
     * @throws NotImplementedError
     */
    public static function getFields()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Batch add records to this model.
     *
     * Example:
     *
     * Managers::createMany([
     *   [ 'first_name' => 'Peter', 'last_name' => 'Smith' ],
     *   [ 'first_name' => 'Joe', 'last_name' => 'Peterson' ],
     *   [ 'first_name' => 'Eric', 'last_name' => 'Miller' ],
     * ]);
     *
     * In case of polimorph models, key of each records should be class name of the particular records:
     *
     * ProjectObjects::add([
     *   [ 'type' => 'Task', 'project_id' => 12, 'name' => 'Do 100 pushups' ],
     *   [ 'type' => 'Discussion', 'project_id' => 12, 'name' => 'April fools', 'body' => 'Should we do something crazy this year?' ],
     * ]);
     *
     * Records added using this function will be automatically saved and returned.
     *
     * @param  array        $records
     * @param  bool         $save
     * @return DataObject[]
     * @throws Exception
     */
    public static function createMany(array $records, $save = true)
    {
        $result = [];

        try {
            DB::beginWork('Batch add records @ ' . __CLASS__);

            foreach ($records as $record) {
                $result[] = static::create($record, $save);
            }

            DB::commit('Records added @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to add records @ ' . __CLASS__);
            throw $e;
        }

        return $result;
    }

    // ---------------------------------------------------
    //  Magic
    // ---------------------------------------------------

    /**
     * Create a new instance from attributes.
     *
     * Note: In case of polymorh model, 'type' attribute is required and it will determine which exact instance this
     * method will create and return. Example:
     *
     * ProjectObjects::create([ 'type' => 'Milestone', 'name' => 'First Sprint' ]);
     *
     * @param  array             $attributes
     * @param  bool              $save
     * @param  bool              $announce
     * @return DataObject
     * @throws InvalidParamError
     */
    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $class_name = static::getInstanceClassNameFrom() == self::CLASS_NAME_FROM_FIELD
            ? self::getInstanceClassNameFromAttributes($attributes)
            : static::getInstanceClassName();

        /** @var DataObject $instance */
        $instance = new $class_name();

        if ($attributes && is_foreachable($attributes)) {
            foreach ($attributes as $k => $v) {
                if ($instance->fieldExists($k)) {
                    if (str_ends_with($k, '_id')) {
                        // @TODO Trick to get all FK-s casted to int. This should be handled by
                        // @TODO DataObject::setFieldValue() actually (if field can't be NULL, cast it before value is
                        // @TODO remembered)
                        $v = (int) $v;
                    }

                    $instance->setFieldValue($k, $v);
                } else {
                    $instance->setAttribute($k, $v);
                }
            }
        }

        if ($save) {
            $instance->save();

            DataObjectPool::introduce($instance);
        }

        return $instance;
    }

    /**
     * Return whether instance class name should be loaded from a field, or based on table name.
     *
     * @return string
     * @throws NotImplementedError
     */
    public static function getInstanceClassNameFrom()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Get instance class name from attributes.
     *
     * @param  array               $attributes
     * @return string
     * @throws InvalidParamError
     * @throws NotImplementedError
     */
    protected static function getInstanceClassNameFromAttributes($attributes)
    {
        $class_name = isset($attributes['type']) && $attributes['type'] ? $attributes['type'] : null;

        if (is_string($class_name) && class_exists($class_name)) {
            $instance_class_name = static::getInstanceClassName();

            if ($class_name != $instance_class_name && !is_subclass_of($class_name, $instance_class_name)) {
                throw new InvalidParamError('attributes[type]', $class_name, "Class '$class_name' does not extend '$instance_class_name'");
            }
        } else {
            throw new InvalidParamError('attributes[type]', $class_name, 'Value of "type" field is required for this model');
        }

        return $class_name;
    }

    /**
     * Update an instance.
     *
     * @param  DataObject $instance
     * @param  array      $attributes
     * @param  bool       $save
     * @return DataObject
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        if ($attributes && is_foreachable($attributes)) {
            foreach ($attributes as $k => $v) {
                if ($instance->fieldExists($k)) {
                    if (str_ends_with($k, '_id')) {
                        $v = (int) $v; // @TODO Trick to get all FK-s casted to int. This should be handled by DataObject::setFieldValue() actually (if field can't be NULL, cast it before value is remembered)
                    }

                    $instance->setFieldValue($k, $v);
                } else {
                    $instance->setAttribute($k, $v);
                }
            }
        }

        if ($save) {
            $instance->save();
        }

        return $instance;
    }

    /**
     * Scrap an instance.
     *
     * @param  DataObject      $instance
     * @param  bool            $force_delete
     * @return DataObject|bool
     */
    public static function scrap(DataObject &$instance, $force_delete = false)
    {
        if ($instance instanceof ITrash && empty($force_delete)) {
            $instance->moveToTrash();

            return $instance;
        } else {
            $instance->delete();

            return true;
        }
    }

    /**
     * Restore given instance to active state.
     *
     * @param  DataObject $instance
     * @return DataObject
     */
    public static function &reactivate(Dataobject &$instance)
    {
        DB::transact(function () use (&$instance) {
            if ($instance instanceof ITrash && $instance->getIsTrashed()) {
                $instance->restoreFromTrash();
            }

            if ($instance instanceof IArchive && $instance->getIsArchived()) {
                $instance->restoreFromArchive();
            }
        });

        return $instance;
    }

    /**
     * Find records where fields match the provided values.
     *
     * Example:
     *
     * Projects::findBy('created_by_id', 1);
     * Projects::findBy([ 'created_by_id' => 1, 'category_id' => null, 'label_id' => 15 ]);
     *
     * @param  string|array               $field
     * @param  mixed                      $value
     * @return DBResult|Dataobject[]|null
     */
    public static function findBy($field, $value = null)
    {
        if (is_array($field)) {
            $conditions = [];

            foreach ($field as $k => $v) {
                $conditions[] = self::fieldAndValueForFindBy($k, $v);
            }

            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = self::fieldAndValueForFindBy($field, $value);
        }

        return static::find(['conditions' => $conditions]);
    }

    /**
     * @param  string            $field
     * @param  mixed             $value
     * @return string
     * @throws InvalidParamError
     */
    private static function fieldAndValueForFindBy($field, $value)
    {
        if (is_array($value)) {
            if (count($value) > 0) {
                return DB::prepare("$field IN (?)", $value);
            } else {
                throw new InvalidParamError('value', $value, '$value can not be an empty array');
            }
        } else {
            return $value === null ? "$field IS NULL" : DB::prepare("$field = ?", $value);
        }
    }

    /**
     * Do a SELECT query over database with specified arguments.
     *
     * This function can return single instance or array of instances that match
     * requirements provided in $arguments associative array
     *
     * $arguments is an associative array with following fields (all optional):
     *
     *  - one        - select first row
     *  - conditions - additional conditions
     *  - group      - group by string
     *  - having     - having string
     *  - order      - order by string
     *  - offset     - limit offset, valid only if limit is present
     *  - limit      - number of rows that need to be returned
     *
     * @param  array                                 $arguments
     * @return DBResult|DataObject[]|DataObject|null
     */
    public static function find($arguments = null)
    {
        if ($arguments && isset($arguments['one']) && $arguments['one']) {
            return static::findOneBySQL(static::prepareSelectFromArguments($arguments));
        } else {
            return static::findBySQL(static::prepareSelectFromArguments($arguments));
        }
    }

    /**
     * Find a single instance by SQL.
     *
     * @return DataObject
     * @throws Error
     * @throws InvalidParamError
     */
    public static function findOneBySql()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            throw new InvalidParamError('arguments', $arguments, 'DataManager::findOneBySql() function requires at least SQL query to be provided');
        }

        $sql = array_shift($arguments);

        if (count($arguments)) {
            $sql = DB::getConnection()->prepare($sql, $arguments);
        }

        if ($row = DB::executeFirstRow($sql)) {
            switch (static::getInstanceClassNameFrom()) {
                case self::CLASS_NAME_FROM_FIELD:
                    $class_name = $row[static::getInstanceClassNameFromField()];
                    break;
                case self::CLASS_NAME_FROM_TABLE:
                    $class_name = static::getInstanceClassName();
                    break;
                default:
                    throw new Error('Unknown load instance class name from method: ' . static::getInstanceClassNameFrom());
            }

            /** @var DataObject $item */
            $item = new $class_name();
            $item->loadFromRow($row, true);

            return $item;
        } else {
            return null;
        }
    }

    /**
     * Return name of the field from which we will read instance class.
     *
     * @return string
     * @throws NotImplementedError
     */
    public static function getInstanceClassNameFromField()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Prepare SELECT query string from arguments and table name.
     *
     * @param  array  $arguments
     * @return string
     */
    public static function prepareSelectFromArguments($arguments = null)
    {
        $one = (bool) (isset($arguments['one']) && $arguments['one']);
        $conditions = isset($arguments['conditions']) ? DB::prepareConditions($arguments['conditions']) : '';
        $group_by = isset($arguments['group']) ? $arguments['group'] : '';
        $having = isset($arguments['having']) ? $arguments['having'] : '';
        $order_by = isset($arguments['order']) ? $arguments['order'] : static::getDefaultOrderBy();
        $offset = isset($arguments['offset']) ? (int) $arguments['offset'] : 0;
        $limit = isset($arguments['limit']) ? (int) $arguments['limit'] : 0;

        if ($one && $offset == 0 && $limit == 0) {
            $limit = 1; // Narrow the query
        }

        $table_name = static::getTableName();
        $where_string = trim($conditions) == '' ? '' : "WHERE $conditions";
        $group_by_string = trim($group_by) == '' ? '' : "GROUP BY $group_by";
        $having_string = trim($having) == '' ? '' : "HAVING $having";
        $order_by_string = trim($order_by) == '' ? '' : "ORDER BY $order_by";
        $limit_string = $limit > 0 ? "LIMIT $offset, $limit" : '';

        return "SELECT * FROM $table_name $where_string $group_by_string $having_string $order_by_string $limit_string";
    }

    /**
     * Return name of this model.
     *
     * @return string
     * @throws NotImplementedError
     */
    public static function getDefaultOrderBy()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Return object of a specific class by SQL.
     *
     * @return DBResult
     * @throws InvalidParamError
     */
    public static function findBySQL()
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            throw new InvalidParamError('arguments', $arguments, 'DataManager::findOneBySql() function requires at least SQL query to be provided');
        }

        $sql = array_shift($arguments);

        if ($arguments !== null) {
            $sql = DB::getConnection()->prepare($sql, $arguments);
        }

        $class_name_from = static::getInstanceClassNameFrom();

        switch ($class_name_from) {
            case self::CLASS_NAME_FROM_FIELD:
                return DB::getConnection()->execute($sql, null, DB::LOAD_ALL_ROWS, DB::RETURN_OBJECT_BY_FIELD, static::getInstanceClassNameFromField());
            case self::CLASS_NAME_FROM_TABLE:
                return DB::getConnection()->execute($sql, null, DB::LOAD_ALL_ROWS, DB::RETURN_OBJECT_BY_CLASS, static::getInstanceClassName());
            default:
                throw new InvalidParamError('class_name_from', $class_name_from, 'Unexpected value');
        }
    }

    /**
     * Find first record where fields match the provided values.
     *
     * Example:
     *
     * Projects::findOneBy('created_by_id', 1);
     * Projects::findOneBy([ 'created_by_id' => 1, 'category_id' => null, 'label_id' => 15 ]);
     *
     * @param  string|array    $field
     * @param  mixed           $value
     * @return Dataobject|null
     */
    public static function findOneBy($field, $value = null)
    {
        if (is_array($field)) {
            $conditions = [];

            foreach ($field as $k => $v) {
                $conditions[] = self::fieldAndValueForFindBy($k, $v);
            }

            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = self::fieldAndValueForFindBy($field, $value);
        }

        return static::find(['conditions' => $conditions, 'one' => true]);
    }

    /**
     * Return multiple records by their ID-s.
     *
     * @param  array    $ids
     * @param  bool     $ordered_by_ids
     * @return DBResult
     */
    public static function findByIds($ids, $ordered_by_ids = false)
    {
        if ($ordered_by_ids) {
            $escaped_ids = DB::escape($ids);

            return static::findBySQL('SELECT * FROM ' . static::getTableName() . " WHERE id IN ($escaped_ids) ORDER BY FIELD (id, $escaped_ids)");
        } else {
            return static::find(['conditions' => ['id IN (?)', $ids]]);
        }
    }

    /**
     * Return paginated result.
     *
     * This function will return paginated result as array. First element of
     * returned array is array of items that match the request. Second parameter
     * is Pager class instance that holds pagination data (total pages, current
     * and next page and so on)
     *
     * @param  array $arguments
     * @param  int   $page
     * @param  int   $per_page
     * @return array
     */
    public static function paginate($arguments = null, $page = 1, $per_page = 10)
    {
        if (empty($arguments)) {
            $arguments = [];
        }

        $arguments['limit'] = $per_page;
        $arguments['offset'] = ($page - 1) * $per_page;

        $conditions = isset($arguments['conditions']) && $arguments['conditions'] ? $arguments['conditions'] : null;

        return [static::find($arguments), new DBResultPager(static::count($conditions), $page, $per_page)];
    }

    /**
     * Return number of rows in this table.
     *
     * @param  string $conditions Query conditions
     * @return int
     */
    public static function count($conditions = null)
    {
        $table_name = static::getTableName();

        $conditions = trim(DB::prepareConditions($conditions));

        if ($conditions) {
            return DB::executeFirstCell("SELECT COUNT(id) AS 'row_count' FROM $table_name WHERE $conditions");
        } else {
            return DB::executeFirstCell("SELECT COUNT(id) AS 'row_count' FROM $table_name");
        }
    }

    /**
     * Return object by ID.
     *
     * @param  mixed             $id
     * @return DataObject
     * @throws InvalidParamError
     */
    public static function findById($id)
    {
        if (empty($id)) {
            return null;
        } elseif (is_numeric($id)) {
        } else {
            throw new InvalidParamError('id', $id, '$id can only be a number');
        }

        $table_name = static::getTableName();

        $cached_row = AngieApplication::cache()->get(static::getCacheKeyForObject($id), function () use ($table_name, $id) {
            return DB::executeFirstRow("SELECT * FROM $table_name WHERE id = ? LIMIT 0, 1", $id);
        });

        if ($cached_row) {
            $class_name_from = static::getInstanceClassNameFrom();

            switch ($class_name_from) {
                case self::CLASS_NAME_FROM_FIELD:
                    $class_name = $cached_row[static::getInstanceClassNameFromField()];
                    break;
                case self::CLASS_NAME_FROM_TABLE:
                    $class_name = static::getInstanceClassName();
                    break;
                default:
                    throw new InvalidParamError('class_name_from', $class_name_from, 'Unexpected value');
            }

            /** @var DataObject $item */
            $item = new $class_name();
            $item->loadFromRow($cached_row);

            return $item;
        } else {
            return null;
        }
    }

    /**
     * Get cache key for a given object.
     *
     * @param  DataObject|int    $object_or_object_id
     * @param  mixed             $subnamespace
     * @return array
     * @throws InvalidParamError
     */
    public static function getCacheKeyForObject($object_or_object_id, $subnamespace = null)
    {
        $instance_class = static::getInstanceClassName();

        if ($object_or_object_id instanceof $instance_class) {
            return get_data_object_cache_key(static::getModelName(true), $object_or_object_id->getId(), $subnamespace);
        } elseif (is_numeric($object_or_object_id)) {
            return get_data_object_cache_key(static::getModelName(true), $object_or_object_id, $subnamespace);
        }

        throw new InvalidParamError('object_or_object_id', $object_or_object_id, "object_or_object_id needs to either instance of $instance_class or ID");
    }

    /**
     * Return model level cache key.
     *
     * @param  array|string|null $subnamespace
     * @return array
     */
    public static function getCacheKey($subnamespace = null)
    {
        $key = ['models', static::getModelName(true)];

        if ($subnamespace) {
            $subnamespace = (array) $subnamespace;

            if (count($subnamespace)) {
                $key = array_merge($key, $subnamespace);
            }
        }

        return $key;
    }

    /**
     * Delete all rows that match given conditions.
     *
     * @param  string $conditions Query conditions
     * @return bool
     */
    public static function delete($conditions = null)
    {
        $table_name = static::getTableName();

        if ($conditions = trim(DB::prepareConditions($conditions))) {
            return (bool) DB::execute("DELETE FROM $table_name WHERE $conditions");
        } else {
            return (bool) DB::execute("DELETE FROM $table_name");
        }
    }

    /**
     * Drop records by parents.
     *
     * @param  array|null          $parents
     * @throws NotImplementedError
     */
    public static function deleteByParents($parents)
    {
        if (static::fieldExists('parent_type') && static::fieldExists('parent_id')) {
            $conditions = static::typeIdsMapToConditions($parents);

            if ($conditions) {
                DB::execute('DELETE FROM ' . static::getTableName() . ' WHERE ' . $conditions);
            }
        } else {
            throw new NotImplementedError(__METHOD__, 'This model does not have parent_type and parent_id fields');
        }
    }

    /**
     * Prepare WHERE part based on a type => IDs map.
     *
     * @param  array       $type_ids_map
     * @param  string      $operation
     * @param  string      $parent_type_field
     * @param  string      $parent_id_field
     * @return string|null
     */
    public static function typeIdsMapToConditions($type_ids_map, $operation = 'OR', $parent_type_field = 'parent_type', $parent_id_field = 'parent_id')
    {
        if ($type_ids_map && is_foreachable($type_ids_map)) {
            $result = [];

            $table_name = static::getTableName();
            foreach ($type_ids_map as $type => $ids) {
                $result[] = DB::prepare("($table_name.$parent_type_field = ? AND $table_name.$parent_id_field IN (?))", $type, $ids);
            }

            return '(' . implode(" $operation ", $result) . ')';
        }

        return null;
    }

    // ---------------------------------------------------
    //  Objects and traits
    // ---------------------------------------------------

    /**
     * Return new collection.
     *
     * @param  string          $collection_name
     * @param  User|null       $user
     * @return ModelCollection
     */
    public static function prepareCollection($collection_name, $user)
    {
        return new ModelCollection($collection_name, static::getModelName());
    }

    /**
     * Return trait names by object.
     *
     * @param  ApplicationObject $object
     * @return array
     */
    public static function getTraitNamesByObject(ApplicationObject $object)
    {
        $class = get_class($object);

        if (!array_key_exists($class, self::$traits_by_object)) {
            self::$traits_by_object[$class] = [];

            self::recursiveGetTraitNames(new ReflectionClass($class), self::$traits_by_object[$class]);
        }

        return static::$traits_by_object[$class];
    }

    /**
     * Recursively get trait names for the given class.
     *
     * @param ReflectionClass $class
     * @param array           $trait_names
     */
    private static function recursiveGetTraitNames(ReflectionClass $class, &$trait_names)
    {
        $trait_names = array_merge($trait_names, $class->getTraitNames());

        if ($class->getParentClass()) {
            static::recursiveGetTraitNames($class->getParentClass(), $trait_names);
        }
    }

    /**
     * Return true if we have a valid $manager_class.
     *
     * @param  string $manager_class
     * @return bool
     */
    public static function isManagerClass($manager_class)
    {
        return (new \ReflectionClass($manager_class))->isSubclassOf(self::class);
    }
}
