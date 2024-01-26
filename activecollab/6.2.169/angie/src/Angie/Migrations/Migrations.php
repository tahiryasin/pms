<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Migrations;

use Angie\Migrations\Errors\MigrationDnxError;
use Angie\Migrations\Errors\MigrationEnvError;
use AngieModelMigration;
use AngieModelMigrationDiscoverer;
use AngieModule;
use DB;
use InvalidParamError;

if (!class_exists(AngieModelMigration::class, false)) {
    require_once ANGIE_PATH . '/classes/application/migrations/AngieModelMigration.class.php';
}

if (!class_exists(AngieModelMigrationDiscoverer::class, false)) {
    require_once ANGIE_PATH . '/classes/application/migrations/AngieModelMigrationDiscoverer.class.php';
}

if (!class_exists(MigrationDnxError::class, false)) {
    require_once __DIR__  . '/Errors/MigrationDnxError.php';
}

if (!class_exists(MigrationEnvError::class, false)) {
    require_once __DIR__  . '/Errors/MigrationEnvError.php';
}

class Migrations implements MigrationsInterface
{
    /**
     * Loaded list of migration scripts.
     *
     * @var array
     */
    private $scripts = false;

    /**
     * Migrate the database up.
     *
     * @param  string        $to_version
     * @param  callable|null $output
     * @return array
     */
    public function up($to_version = null, callable $output = null)
    {
        $current_thread_stack = (int) DB::executeFirstCell('select @@thread_stack');
        $minimum_thread_stack = 1024 * 256;
        if ($current_thread_stack < $minimum_thread_stack) {
            throw new MigrationEnvError("Required 'thread_stack' value is " . format_file_size($minimum_thread_stack) . ' and currently value is ' . format_file_size($current_thread_stack));
        }

        if (empty($to_version)) {
            $to_version = APPLICATION_VERSION;
        }

        $batch = [];

        foreach ($this->getScripts($to_version) as $migrations) {
            foreach ($migrations as $migration) {
                if ($migration instanceof AngieModelMigration) {
                    $this->executeMigrationUp($migration, $batch, $output);
                }
            }
        }

        return $batch;
    }

    /**
     * Return a list of migration scripts.
     *
     * @param  string                $for_version
     * @return AngieModelMigration[]
     */
    public function getScripts($for_version)
    {
        if (empty($for_version)) {
            throw new InvalidParamError('for_version', $for_version, 'for_version parameter required');
        }

        if ($this->scripts === false) {
            $this->scripts = AngieModelMigrationDiscoverer::discover($for_version); // Discover migration scripts for currently installed version
        }

        return $this->scripts;
    }

    /**
     * Trigger one migration up.
     *
     * @param AngieModelMigration $migration
     * @param array               $batch
     * @param callable|null       $output
     */
    private function executeMigrationUp(AngieModelMigration $migration, array &$batch, callable $output = null)
    {
        $migration_name = get_class($migration);

        if (in_array($migration_name, $batch)) {
            return;
        }

        $changeset = $migration->getChangeset();

        if ($migration->isExecuted()) {
            return;
        }

        $execute_after_migrations = $migration->getExecuteAfter();

        if (is_foreachable($execute_after_migrations)) {
            if ($output) {
                call_user_func($output, "<comment>Notice:</comment> Migration <comment>$migration_name</comment> needs to be executed after these migrations: " . implode(', ', $execute_after_migrations));
            }

            foreach ($execute_after_migrations as $execute_after_migration_name) {
                $execute_after_migration = $this->getScript($changeset, $execute_after_migration_name);

                if ($execute_after_migration instanceof AngieModelMigration) {
                    $this->executeMigrationUp($execute_after_migration, $batch, $output);
                } else {
                    throw new MigrationDnxError($execute_after_migration_name, $changeset);
                }
            }
        }

        if ($output) {
            call_user_func($output, "<info>OK:</info> Executing <comment>$migration_name</comment>");
        }

        $migration->up();
        $migration->setAsExecuted();

        $batch[] = $migration_name;
    }

    /**
     * Return a particular script.
     *
     * @param  string              $changeset
     * @param  string              $script
     * @return AngieModelMigration
     */
    public function getScript($changeset, $script)
    {
        if ($this->scripts === false) {
            $this->getScripts(APPLICATION_VERSION);
        }

        return !empty($this->scripts[$changeset][$script])
            && $this->scripts[$changeset][$script] instanceof AngieModelMigration
            ? $this->scripts[$changeset][$script]
            : null;
    }

    /**
     * Return scripts form a given module.
     *
     * @param  AngieModule           $module
     * @return AngieModelMigration[]
     */
    public function getScriptsInModule(AngieModule $module)
    {
        return AngieModelMigrationDiscoverer::discoverFromPaths([$module->getPath() . '/migrations']);
    }

    /**
     * Return time stamp from a given change-set name.
     *
     * @param  string      $name
     * @return string|bool
     */
    public function getChangesetTimestamp($name)
    {
        $matches = [];

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})-(.*)$/', $name, $matches)) {
            return "$matches[1]-$matches[2]-$matches[3]";
        }

        return false;
    }
}
