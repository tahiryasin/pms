<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Error;

/**
 * Angie application model.
 *
 * @package angie.library.application
 */
class AngieApplicationModel
{
    /**
     * List of loaded models.
     *
     * @var AngieFrameworkModel[]|AngieModuleModel[]
     */
    private static $models = [];

    /**
     * List of loaded frameworks and modules.
     *
     * @var array
     */
    private static $loaded_frameworks = [];
    private static $loaded_modules = [];
    /**
     * List of queries that need to be executed to reinitialize database for testing.
     *
     * @var array
     */
    private static $test_queries = [];

    /**
     * Load framework and module models.
     *
     * @param array $framework_names
     * @param array $module_names
     */
    public static function load($framework_names, $module_names)
    {
        if (count(self::$loaded_frameworks) && count(self::$loaded_modules)) {
            return; // Model already loaded
        }

        // Load framework models
        foreach ($framework_names as $framework_name) {
            $framework_class = Angie\Inflector::camelize($framework_name) . 'Framework';

            $file = ANGIE_PATH . "/frameworks/$framework_name/$framework_class.php";
            if (is_file($file)) {
                require_once $file;

                $framework = new $framework_class();
                if ($framework instanceof AngieFramework) {
                    self::$loaded_frameworks[] = $framework_name;

                    if ($framework->getModel() instanceof AngieFrameworkModel) {
                        self::$models[$framework->getName()] = $framework->getModel();
                    }
                }
            }
        }

        // Load module models
        foreach ($module_names as $module_name) {
            $module_class = Angie\Inflector::camelize($module_name) . 'Module';

            $file = APPLICATION_PATH . "/modules/$module_name/$module_class.php";

            if (!is_file($file)) {
                $file = CUSTOM_PATH . "/modules/$module_name/$module_class.php";
            }

            if (is_file($file)) {
                require_once $file;

                $module = new $module_class();
                if ($module instanceof AngieModule) {
                    self::$loaded_modules[] = $module_name;

                    if ($module->getModel() instanceof AngieModuleModel) {
                        self::$models[$module->getName()] = $module->getModel();
                    }
                }
            }
        }
    }

    /**
     * Revert to model's original state.
     *
     * @param string $environment
     */
    public static function revert($environment = null)
    {
        self::clear();
        self::init($environment);
    }

    public static function clear(bool $drop_tables = false): void
    {
        if (!empty(self::$loaded_frameworks) && !empty(self::$loaded_modules)) {
            foreach (self::getTables() as $table) {
                if ($table->exists()) {
                    if ($drop_tables) {
                        $table->delete();
                    } else {
                        $table->truncate();
                    }
                }
            }
        } else {
            throw new Error('Model not loaded');
        }
    }

    /**
     * Return all tables.
     *
     * @return DBTable[]
     */
    public static function getTables()
    {
        $tables = [];

        foreach (self::$models as $model) {
            foreach ($model->getTables() as $k => $v) {
                $tables[$k] = $v;
            }
        }

        return $tables;
    }

    /**
     * Initialize all loaded frameworks and modules for given environment.
     *
     * @param string $environment
     */
    public static function init($environment = null)
    {
        if ($environment === 'test' && count(self::$test_queries)) {
            foreach (self::$test_queries as $query) {
                DB::execute($query);
            }
        } else {
            $query_log_count = $environment === 'test' ? DB::getQueryCount() : 0;

            foreach (self::$models as &$model) {
                $model->createTables();
            }
            unset($model);

            foreach (self::$models as &$model) {
                $model->loadInitialData();
            }
            unset($model);

            $paths_to_scan = [
                ANGIE_PATH . '/migrations',
                APPLICATION_PATH . '/migrations',
            ];

            if (!class_exists('AngieModelMigrationDiscoverer', false) && !class_exists('AngieModelMigration', false)) {
                require_once ANGIE_PATH . '/classes/application/migrations/AngieModelMigration.class.php';
                require_once ANGIE_PATH . '/classes/application/migrations/AngieModelMigrationDiscoverer.class.php';
            }

            $migrations = [];

            /** @var AngieModelMigration[] $scripts */
            foreach (AngieModelMigrationDiscoverer::discoverFromPaths($paths_to_scan) as $scripts) {
                foreach ($scripts as $script) {
                    $changeset = $script->getChangeset();

                    $changeset_timestamp = $script->getChangesetTimestamp($changeset);
                    $changeset_name = substr($changeset, 11);

                    $migrations[] = DB::prepare('(?, ?, ?, UTC_TIMESTAMP())', get_class($script), $changeset_timestamp, $changeset_name);
                }
            }

            DB::execute('REPLACE INTO executed_model_migrations (migration, changeset_timestamp, changeset_name, executed_on) VALUES ' . implode(', ', $migrations));

            if ($environment === 'test') {
                self::$test_queries = [];

                foreach (array_slice(DB::getAllQueries(), $query_log_count) as $current_query) {
                    if (!self::isInitialDataQuery($current_query)) {
                        continue;
                    }

                    self::$test_queries[] = $current_query;
                }
            }
        }
    }

    private static function isInitialDataQuery(string $query): bool
    {
        $blocked_queries = [
            'CREATE TABLE',
            'CREATE TRIGGER',
            'ALTER TABLE',
        ];

        foreach ($blocked_queries as $blocked_query) {
            if (str_starts_with($query, $blocked_query)) {
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------------------
    //  Getters
    // ---------------------------------------------------

    /**
     * Returns true if this model is empty (there are no model instances loaded).
     *
     * @return bool
     */
    public static function isEmpty()
    {
        return empty(self::$models);
    }

    /**
     * Return specific table.
     *
     * @param  string            $table_name
     * @return DBTable
     * @throws InvalidParamError
     * @throws Exception
     */
    public static function &getTable($table_name)
    {
        foreach (self::$models as $model) {
            try {
                $table = $model->getTable($table_name);
                if ($table instanceof DBTable) {
                    return $table;
                }
            } catch (InvalidParamError $e) {
                // Skip name error
            } catch (Exception $e) {
                throw $e;
            }
        }

        throw new InvalidParamError('table_name', $table_name, "Table '$table_name' is not defined in any of the models");
    }

    /**
     * Return all model builders.
     *
     * @return AngieFrameworkModelBuilder[]
     */
    public static function getModelBuilders()
    {
        $model_builders = [];

        foreach (self::$models as $model) {
            foreach ($model->getModelBuilders() as $k => $v) {
                $model_builders[$k] = $v;
            }
        }

        return $model_builders;
    }

    /**
     * Return model builder for specific table.
     *
     * @param  string                     $for_table_name
     * @return AngieFrameworkModelBuilder
     * @throws InvalidParamError
     * @throws Exception
     */
    public static function &getModelBuilder($for_table_name)
    {
        foreach (self::$models as $model) {
            try {
                $model_builder = $model->getModelBuilder($for_table_name);
                if ($model_builder instanceof AngieFrameworkModelBuilder) {
                    return $model_builder;
                }
            } catch (InvalidParamError $e) {
                // Skip name error
            } catch (Exception $e) {
                throw $e;
            }
        }

        throw new InvalidParamError('for_table_name', $for_table_name, "Model builder is not defined for '$for_table_name' table in any of the models");
    }
}
