<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Angie framework model implementation.
 *
 * @package angie.library.application
 */
abstract class AngieFrameworkModel
{
    /**
     * Parent framework or module.
     *
     * @var AngieFramework
     */
    protected $parent;

    /**
     * List of tables used by this framework.
     *
     * @var DBTable[]
     */
    protected $tables = [];

    /**
     * Array of model builders, indexed by table name.
     *
     * @var AngieFrameworkModelBuilder[]
     */
    protected $model_builders = [];
    /**
     * @var array
     */
    private $config_options_to_insert = [];
    /**
     * @var array
     */
    private $memories_to_insert = [];

    /**
     * Construct framework or module model instance.
     *
     * @param  AngieFramework       $parent
     * @throws InvalidInstanceError
     */
    public function __construct(AngieFramework $parent)
    {
        if ($parent instanceof AngieFramework) {
            $this->parent = $parent;
        } else {
            throw new InvalidInstanceError('parent', $parent, 'AngieFramework');
        }
    }

    /**
     * Add table that is loaded from a definition AMQPChannel.
     *
     * @param  string  $table_name
     * @return DBTable
     */
    public function &addTableFromFile($table_name)
    {
        return $this->addTable($this->loadTableDefinion($table_name));
    }

    /**
     * Add table to the list of tables used by this framework or model.
     *
     * @param  DBTable $table
     * @return DBTable
     */
    public function &addTable(DBTable $table)
    {
        $this->tables[$table->getName()] = $table;

        return $this->tables[$table->getName()];
    }

    /**
     * Load table from a file file.
     *
     * @param  string       $table_name
     * @return DBTable
     * @throws FileDnxError
     */
    public function loadTableDefinion($table_name)
    {
        $class = new ReflectionClass($this);

        $table_file = dirname($class->getFileName()) . "/table.{$table_name}.php";

        if (is_file($table_file)) {
            return require $table_file;
        } else {
            throw new FileDnxError($table_file, "Table '$table_name' definition was not found");
        }
    }

    /**
     * Add model from a file.
     *
     * @param  string                     $table_name
     * @return AngieFrameworkModelBuilder
     */
    public function &addModelFromFile($table_name)
    {
        return $this->addModel($this->loadTableDefinion($table_name));
    }

    /**
     * Add model.
     *
     * @param  DBTable                    $table
     * @return AngieFrameworkModelBuilder
     */
    public function &addModel(DBTable $table)
    {
        $this->tables[$table->getName()] = $table;

        $this->model_builders[$table->getName()] = new AngieFrameworkModelBuilder($this, $table);

        return $this->model_builders[$table->getName()];
    }

    /**
     * Return all tables defined by this model.
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Return single table.
     *
     * @param  string            $name
     * @return DBTable
     * @throws InvalidParamError
     */
    public function getTable($name)
    {
        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        } else {
            throw new InvalidParamError('name', $name, "Table '$name' is not defined in this model");
        }
    }

    /**
     * Return parent module or framework.
     *
     * @return AngieFramework
     */
    public function getParent()
    {
        return $this->parent;
    }

    // ---------------------------------------------------
    //  Install and initialize
    // ---------------------------------------------------

    /**
     * Return all model builders defined by this model.
     *
     * @return AngieFrameworkModelBuilder[]
     */
    public function getModelBuilders()
    {
        return $this->model_builders;
    }

    /**
     * Return specific model builder.
     *
     * @param  string                     $for_table_name
     * @return AngieFrameworkModelBuilder
     * @throws InvalidParamError
     */
    public function getModelBuilder($for_table_name)
    {
        if (isset($this->model_builders[$for_table_name])) {
            return $this->model_builders[$for_table_name];
        } else {
            throw new InvalidParamError('for_table_name', $for_table_name, "Model builder is not defined for '$for_table_name' table in this model");
        }
    }

    /**
     * Create framework tables.
     */
    public function createTables()
    {
        foreach ($this->tables as &$table) {
            $table->save();
        }
    }

    /**
     * Enter description here...
     */
    public function dropTables()
    {
        foreach ($this->tables as &$table) {
            DB::execute('DROP TABLE IF EXISTS ' . $table->getName());
        }
    }

    /**
     * Load initial framework data.
     */
    public function loadInitialData()
    {
        if (count($this->config_options_to_insert)) {
            DB::execute('INSERT INTO config_options (`name`, `value`) VALUES ' . implode(', ', $this->config_options_to_insert));
            $this->config_options_to_insert = [];
        }

        if (count($this->memories_to_insert)) {
            DB::execute('INSERT INTO memories (`key`, `value`) VALUES ' . implode(', ', $this->memories_to_insert));
            $this->memories_to_insert = [];
        }
    }

    /**
     * Load data to table.
     *
     * @param  string    $table
     * @param  array     $rows
     * @throws Exception
     */
    public function loadTableData($table, array $rows)
    {
        switch (count($rows)) {
            case 0:
                return;
            case 1:
                DB::execute("INSERT INTO $table (" . implode(', ', array_keys($rows[0])) . ') VALUES (?)', $rows[0]);
                break;
            default:
                try {
                    DB::beginWork("Loading table data for '" . $this->parent->getName() . "' @ " . __CLASS__);

                    foreach ($rows as $row) {
                        DB::execute("INSERT INTO $table (" . implode(', ', array_keys($row)) . ') VALUES (?)', $row);
                    }

                    DB::commit("Table data loaded for '" . $this->parent->getName() . "' @ " . __CLASS__);
                } catch (Exception $e) {
                    DB::rollback("Failed to load table data for '" . $this->parent->getName() . "' @ " . __CLASS__);
                    throw $e;
                }
        }
    }

    /**
     * Return list of steps that need to be executed for this framework to or
     * module to be updated to the latest version.
     *
     * @param array
     */
    public function getUpgradeSteps()
    {
    }

    /**
     * Execute specified upgrade step.
     *
     * This function validates step name before executing it
     *
     * @param  string            $step_name
     * @throws InvalidParamError
     */
    public function executeUpgradeStep($step_name)
    {
        if (preg_match('/^v([0-9]*)_(.*)$/', $step_name) && method_exists($this, $step_name)) {
            $this->$step_name();
        } else {
            throw new InvalidParamError('step_name', $step_name, "'$step_name' is not a valid upgrade function");
        }
    }

    // ---------------------------------------------------
    //  Helper options
    // ---------------------------------------------------

    /**
     * Create new configuration option.
     *
     * @param string $name
     * @param mixed  $default
     */
    protected function addConfigOption($name, $default = null)
    {
        $this->config_options_to_insert[] = DB::prepare('(?, ?)', $name, ($default === null ? null : serialize($default)));
    }

    /**
     * Add a new record to memories table.
     *
     * @param string $name
     * @param mixed  $default
     */
    protected function addMemory($name, $default = null)
    {
        $this->memories_to_insert[] = DB::prepare('(?, ?)', $name, ($default === null ? null : serialize($default)));
    }

    // ---------------------------------------------------
    //  Upgrade
    // ---------------------------------------------------

    /**
     * Create a new object in a given table, with given properties.
     *
     * This function is specific because it creates proper records in search
     * index, modification log etc
     *
     * @param  string $table
     * @param  array  $properties
     * @return int
     */
    protected function createObject($table, $properties)
    {
        $to_insert = [];
        foreach ($properties as $k => $v) {
            $to_insert[DB::escapeFieldName($k)] = DB::escape($v);
        }

        DB::execute('INSERT INTO ' . DB::escapeTableName($table) . ' (' . implode(', ', array_keys($to_insert)) . ') VALUES (' . implode(', ', $to_insert) . ')');

        return DB::lastInsertId();
    }

    // ---------------------------------------------------
    //  Utility
    // ---------------------------------------------------

    /**
     * Returns true if current framework or module version is smaller than
     * $version.
     *
     * @param  string $version
     * @return bool
     */
    protected function currentVersionSmallerThan($version)
    {
        return version_compare($this->parent->getVersion(), $version) == -1;
    }
}
