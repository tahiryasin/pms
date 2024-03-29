<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Inflector;

/**
 * Basic, configurable model collection.
 *
 * @package angie.library.database
 */
class ModelCollection extends DataObjectCollection
{
    /**
     * Name of the model that this collection is working with.
     *
     * @var string
     */
    private $model_name;

    /**
     * Collection name.
     *
     * @var string
     */
    private $name;
    /**
     * Cached tag value.
     *
     * @var string
     */
    private $tag = false;
    /**
     * Cached time stamp field name.
     *
     * @var string|bool
     */
    private $timestamp_field = null;

    // ---------------------------------------------------
    //  Etag
    // ---------------------------------------------------
    /**
     * @var int[]
     */
    private $ids = false;
    /**
     * Return model table name.
     *
     * @var string
     */
    private $table_name;
    /**
     * Cached order by value.
     *
     * @var string|null
     */
    private $order_by = false;
    /**
     * Collection conditions.
     *
     * @var string
     */
    private $conditions;
    /**
     * Name of the join table.
     *
     * @var string
     */
    private $join_table;
    /**
     * Join field name.
     *
     * @var string
     */
    private $join_field = 'id';
    private $join_with_field;

    /**
     * @var array|false
     */
    private $join_expressions = false;

    // ---------------------------------------------------
    //  Model interaction
    // ---------------------------------------------------
    /**
     * @var callable
     */
    private $pre_execute_callback;

    /**
     * @param string $name
     * @param string $model_name
     */
    public function __construct($name, $model_name)
    {
        parent::__construct($name);

        $this->model_name = $model_name;
        $this->name = $name;
    }

    /**
     * Return true if this object can be tagged and cached on client side.
     *
     * @return bool|null
     */
    public function canBeTagged()
    {
        return (bool) $this->getTimestampField();
    }

    /**
     * Return timestamp field name.
     *
     * @return string|bool
     */
    public function getTimestampField()
    {
        if ($this->timestamp_field === null) {
            if ($this->modelFieldExist('updated_on')) {
                $this->timestamp_field = 'updated_on';
            } elseif ($this->modelFieldExist('created_on')) {
                $this->timestamp_field = 'created_on';
            } else {
                $this->timestamp_field = false;
            }
        }

        return $this->timestamp_field;
    }

    /**
     * Return true if model field exists.
     *
     * @param  string $field_name
     * @return bool
     */
    public function modelFieldExist($field_name)
    {
        return call_user_func("{$this->model_name}::fieldExists", $field_name);
    }

    /**
     * Return collection etag.
     *
     * @param  IUser  $user
     * @param  bool   $use_cache
     * @return string
     */
    public function getTag(IUser $user, $use_cache = true)
    {
        $timestamp_field = $this->getTimestampField();

        if ($timestamp_field && ($this->tag === false || !$use_cache)) {
            $this->tag = $this->prepareTagFromBits($user->getEmail(), $this->getTimestampHash($timestamp_field));
        }

        return $this->tag;
    }

    /**
     * Return timestamp hash.
     *
     * @param  string $timestamp_field
     * @return string
     */
    public function getTimestampHash($timestamp_field)
    {
        $table_name = $this->getTableName();
        $conditions = $this->conditions ? " WHERE $this->conditions" : '';

        if ($this->count() > 0) {
            if ($join_expression = $this->getJoinExpression()) {
                return sha1(DB::executeFirstCell("SELECT GROUP_CONCAT($table_name.$timestamp_field ORDER BY $table_name.id SEPARATOR ',') AS 'timestamp_hash' FROM $table_name $join_expression $conditions"));
            } else {
                return sha1(DB::executeFirstCell("SELECT GROUP_CONCAT($table_name.$timestamp_field ORDER BY id SEPARATOR ',') AS 'timestamp_hash' FROM $table_name $conditions"));
            }
        }

        return sha1($this->getModelName());
    }

    /**
     * Return model table name.
     *
     * @return mixed
     */
    public function getTableName()
    {
        if (empty($this->table_name)) {
            $this->table_name = call_user_func("{$this->model_name}::getTableName");
        }

        return $this->table_name;
    }

    /**
     * Return number of records that match conditions set by the collection.
     *
     * @return int
     */
    public function count()
    {
        $table_name = $this->getTableName();
        $conditions = $this->conditions ? " WHERE $this->conditions" : '';

        if ($join_expression = $this->getJoinExpression()) {
            return (int) DB::executeFirstCell("SELECT COUNT($table_name.id) FROM $table_name $join_expression $conditions");
        } else {
            return DB::executeFirstCell("SELECT COUNT(id) AS 'row_count' FROM $table_name $conditions");
        }
    }

    public function setSecondJoinExpression($join_expression)
    {
        $this->second_join_expression = $join_expression;
    }

    /**
     * Return join expression.
     *
     * @return string|null
     */
    private function getJoinExpression()
    {
        $expression = '';

        if ($this->join_table && $this->join_field && $this->join_with_field) {
            $expression .= "LEFT JOIN $this->join_table ON " . $this->getTableName() . ".$this->join_field = $this->join_table.$this->join_with_field";
        }
        if ($this->join_expressions) {
            $expression .= " $this->join_expressions";
        }

        return !empty($expression) ? $expression : null;
    }

    /**
     * Return model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->model_name;
    }

    /**
     * Run the query and return DB result.
     *
     * @return DbResult|DataObject[]
     */
    public function execute()
    {
        if (is_callable($this->pre_execute_callback)) {
            $ids = $this->executeIds();
            $ids_count = count($ids);

            if ($ids_count) {
                call_user_func($this->pre_execute_callback, $ids);

                // Don't escape more than 1000 ID-s using DB::escape(), let MySQL do the dirty work instead of PHP
                if ($ids_count <= 1000) {
                    $escaped_ids = DB::escape($ids);

                    return call_user_func("{$this->model_name}::findBySQL", DB::prepare('SELECT * FROM ' . $this->getTableName() . " WHERE id IN ($escaped_ids) ORDER BY FIELD (id, $escaped_ids)"));
                } else {
                    return call_user_func("{$this->model_name}::findBySQL", $this->getSelectSql());
                }
            }

            return null;
        } else {
            return call_user_func("{$this->model_name}::findBySQL", $this->getSelectSql());
        }
    }

    /**
     * Return ID-s of matching records.
     *
     * @return array
     */
    public function executeIds()
    {
        if ($this->ids === false) {
            $this->ids = DB::executeFirstColumn($this->getSelectSql(false));

            if (empty($this->ids)) {
                $this->ids = [];
            }
        }

        return $this->ids;
    }

    /**
     * @param  bool   $all_fields
     * @return string
     */
    private function getSelectSql($all_fields = true)
    {
        $offset = $this->getCurrentPage() !== null ? ($this->getCurrentPage() - 1) * $this->getItemsPerPage() : null;
        $limit = $this->getItemsPerPage();

        $fields = $all_fields ? '*' : 'id';
        $table_name = $this->getTableName();
        $conditions = $this->conditions ? "WHERE $this->conditions" : '';

        if ($order_by = $this->getOrderBy()) {
            $order_by = "ORDER BY $this->order_by";
        } else {
            $order_by = '';
        }

        $limit = is_int($offset) && $limit ? "LIMIT $offset, $limit" : '';

        if ($join_expression = $this->getJoinExpression()) {
            return "SELECT $table_name.$fields FROM $table_name $join_expression $conditions $order_by $limit";
        } else {
            return "SELECT $fields FROM $table_name $conditions $order_by $limit";
        }
    }

    /**
     * Return order by.
     *
     * @return string|null
     */
    public function getOrderBy()
    {
        if ($this->order_by === false) {
            $this->order_by = call_user_func("{$this->model_name}::getDefaultOrderBy");
        }

        return $this->order_by;
    }

    // ---------------------------------------------------
    //  Joining support
    // ---------------------------------------------------

    /**
     * Set how system should order records in this collection.
     *
     * @param  string            $value
     * @throws InvalidParamError
     */
    public function setOrderBy($value)
    {
        if ($value === null || $value) {
            $this->order_by = $value;
        } else {
            throw new InvalidParamError('value', $value, '$value can be NULL or a valid order by value');
        }
    }

    /**
     * Return number of items that will be displayed on the current page of paginated collection (or total, if collection is not paginated).
     *
     * @return int
     */
    public function countIds()
    {
        return count($this->executeIds());
    }

    /**
     * Return conditions.
     *
     * @return string|null
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Set collection conditions.
     *
     * @param  string|array      $conditions
     * @throws InvalidParamError
     */
    public function setConditions($conditions)
    {
        if ($conditions) {
            if (is_string($conditions)) {
                $this->conditions = DB::prepareConditions(func_get_args());

                return;
            } elseif (is_array($conditions)) {
                $this->conditions = DB::prepareConditions($conditions);

                return;
            }
        }

        throw new InvalidParamError('conditions', $conditions, 'Arugment is expected to be a valid conditions array of conditions pattern');
    }

    /**
     * Return join table name.
     *
     * @return string
     */
    public function getJoinTable()
    {
        return $this->join_table;
    }

    /**
     * Set join table name.
     *
     * If $join_field is null, join field will be based on model name. There are two ways to specify it:
     *
     * 1. As string, where value is for target field and it will map with ID column of the source table,
     * 2. As array, where first element is ID in the source table and second element is field in target table
     *
     * @param string            $table_name_without_prefix
     * @param array|string|null $join_field
     */
    public function setJoinTable($table_name_without_prefix, $join_field = null)
    {
        $this->join_table = $table_name_without_prefix;

        if (empty($this->join_with_field)) {
            if (is_array($join_field) && count($join_field) === 2) {
                [$this->join_field, $this->join_with_field] = $join_field;
            } else {
                if (is_string($join_field) && $join_field) {
                    $this->join_with_field = $join_field;
                } else {
                    $this->join_with_field = Inflector::singularize(Inflector::underscore($this->model_name)) . '_id';
                }
            }
        }
    }

    public function setJoinExpressions(array $expressions)
    {
        if ($this->join_expressions === false && count($expressions)) {
            $this->join_expressions = implode(' ', $expressions);
        }
    }

    /**
     * Return join field name.
     *
     * @return string
     */
    public function getJoinField()
    {
        return $this->join_field;
    }

    /**
     * Set join field name.
     *
     * @param string $value
     */
    public function setJoinField($value)
    {
        $this->join_field = $value;
    }

    /**
     * Return join field name.
     *
     * @return string
     */
    public function getJoinWithField()
    {
        return $this->join_with_field;
    }

    /**
     * Set join field name.
     *
     * @param string $value
     */
    public function setJoinWithField($value)
    {
        $this->join_with_field = $value;
    }

    /**
     * Set a callback that will be triggered prior to collection execution.
     *
     * @param callable $callback
     */
    public function setPreExecuteCallback(callable $callback)
    {
        $this->pre_execute_callback = $callback;
    }
}
