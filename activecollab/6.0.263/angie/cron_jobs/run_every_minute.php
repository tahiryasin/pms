<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Run jobs from the jobs queue.
 *
 * @package angie
 */
if (php_sapi_name() != 'cli') {
    die("Error: CLI only\n");
}

if (isset($this) && $this instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
    return;
}

// ---------------------------------------------------
//  Kill the limits
// ---------------------------------------------------

set_time_limit(0);
date_default_timezone_set('UTC');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once APPLICATION_PATH . '/vendor/autoload.php';

// ---------------------------------------------------
//  Dependencies
// ---------------------------------------------------

$container = new \Angie\Container();

$container['environment'] = function () {
    if (defined('APPLICATION_MODE') && APPLICATION_MODE == 'production') {
        return 'production';
    }

    return 'development';
};

$container['app_root'] = function () {
    return ENVIRONMENT_PATH;
};

$container['app_version'] = function ($c) {
    return APPLICATION_VERSION;
};

$container['app_identifier'] = function () {
    return 'ActiveCollab';
};

$container['db_credentials'] = function () {
    $db_port = defined('DB_PORT') ? DB_PORT : 3306;

    return [DB_HOST, $db_port, DB_USER, DB_PASS, DB_NAME];
};

$container['connection'] = function ($c) {
    [$db_host, $db_port, $db_user, $db_pass, $db_name] = $c['db_credentials'];

    return (new \ActiveCollab\DatabaseConnection\ConnectionFactory($c['log']))->mysqli("$db_host:$db_port", $db_user, $db_pass, $db_name, 'utf8mb4');
};

$container['queue'] = function ($c) {
    return new \ActiveCollab\JobsQueue\Queue\MySqlQueue($c['connection'], true, $c['log']);
};

$container['dispatcher'] = function ($c) {
    $dispatcher = new \ActiveCollab\JobsQueue\Dispatcher($c['queue']);
    $dispatcher->registerChannels('maintenance', 'mail', 'search', 'webhook');

    return $dispatcher;
};

$container['log_dir'] = function ($c) {
    return $c['app_root'] . '/logs';
};

$container['log'] = function ($c) {
    $environment = $c['environment'];
    $log_level = $environment == 'production' ? \ActiveCollab\Logger\LoggerInterface::LOG_FOR_PRODUCTION : \ActiveCollab\Logger\LoggerInterface::LOG_FOR_DEBUG;
    $logger_type = $environment === 'production' ? \ActiveCollab\Logger\LoggerInterface::BLACKHOLE : \ActiveCollab\Logger\LoggerInterface::FILE;

    $constructor_arguments = [
        $c['app_identifier'],
        $c['app_version'],
        $environment,
        $log_level,
        $logger_type,
    ];

    if ($logger_type === \ActiveCollab\Logger\LoggerInterface::FILE) {
        $constructor_arguments[] = $c['log_dir'];
    }

    $factory = new \ActiveCollab\Logger\Factory\Factory();

    return call_user_func_array([$factory, 'create'], $constructor_arguments);
};

// ---------------------------------------------------
//  Log
// ---------------------------------------------------

/** @var \ActiveCollab\Logger\LoggerInterface $log */
$log = $container['log'];
$log->flushBufferOnShutdown();

// ---------------------------------------------------
//  Lets remember that we ran the task
// ---------------------------------------------------

// @TODO Refactor when Memories starts using DatabaseConnection

/** @var \ActiveCollab\DatabaseConnection\ConnectionInterface $connection */
$connection = $container['connection'];

if ($connection->executeFirstCell("SELECT COUNT(`id`) AS 'row_count' FROM `memories` WHERE `key` = 'frequently_last_run'")) {
    $connection->execute("UPDATE `memories` SET `value` = ?, `updated_on` = ? WHERE `key` = 'frequently_last_run'", serialize(time()), date('Y-m-d H:i:s'));
} else {
    $connection->execute("INSERT INTO `memories` (`key`, `value`, `updated_on`) VALUES ('frequently_last_run', ?, ?)", serialize(time()), date('Y-m-d H:i:s'));
}

// ---------------------------------------------------
//  Prepare dispatcher and success and error logs
// ---------------------------------------------------

use ActiveCollab\JobsQueue\Jobs\Job;

/** @var \ActiveCollab\JobsQueue\DispatcherInterface $dispatcher */
$dispatcher = $container['dispatcher'];

$jobs_ran = $jobs_failed = [];

$dispatcher->getQueue()->onJobFailure(function (Job $job, Exception $e) use (&$jobs_failed) {
    $job_id = $job->getQueueId();

    if (!in_array($job_id, $jobs_failed)) {
        $jobs_failed[] = $job_id;
    }

    print 'Exception ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    print 'Exception throw at ' . $e->getFile() . ' line ' . $e->getLine() . "\n";
    print $e->getTraceAsString() . "\n";
});

$reference_time = microtime(true);

if ($jobs_count = $dispatcher->getQueue()->count()) {
    print "There are {$dispatcher->getQueue()->count()} jobs in the queue\n";

    // ---------------------------------------------------
    //  Set max execution time for the jobs in queue
    // ---------------------------------------------------

    $max_execution_time = defined('MAX_JOBS_EXECUTION_TIME') && MAX_JOBS_EXECUTION_TIME ? MAX_JOBS_EXECUTION_TIME : 50;

    print "Preparing to work for {$max_execution_time} seconds\n\n";

    $work_until = time() + $max_execution_time; // Assume that we spent 1 second bootstrapping the command

    // ---------------------------------------------------
    //  Enter the execution loop
    // ---------------------------------------------------

    do {
        if ($next_in_line = $dispatcher->getQueue()->nextInLine()) {
            $log->debug('Running job #{job_id} of {job_type} type', [
                'job_type' => get_class($next_in_line),
                'job_id' => $next_in_line->getQueueId(),
            ]);

            print 'Running job #' . $next_in_line->getQueueId() . ' (' . get_class($next_in_line) . ")\n";

            if (method_exists($next_in_line, 'setContainer')) {
                $next_in_line->setContainer($container);
            }

            $dispatcher->getQueue()->execute($next_in_line);

            print "Job #{$next_in_line->getQueueId()} done\n";

            $job_id = $next_in_line->getQueueId();

            if (!in_array($job_id, $jobs_ran)) {
                $jobs_ran[] = $job_id;
            }
        } else {
            break; // No new jobs? Break from the loop
        }
    } while (time() < $work_until);

    // ---------------------------------------------------
    //  Print stats
    // ---------------------------------------------------

    // ---------------------------------------------------
    //  Print stats
    // ---------------------------------------------------

    $execution_stats = [
        'time_limit' => $max_execution_time,
        'exec_time' => round(microtime(true) - $reference_time, 3),
        'jobs_ran' => count($jobs_ran),
        'jobs_failed' => count($jobs_failed),
        'left_in_queue' => $dispatcher->getQueue()->count(),
    ];

    $log->debug('{jobs_ran} jobs ran in {exec_time}s', $execution_stats);

    print "\n";
    print 'Execution stats: ' . $execution_stats['jobs_ran'] . ' ran, ' . $execution_stats['jobs_failed'] . ' failed. ' . $execution_stats['left_in_queue'] . ' left in queue. Executed in ' . $execution_stats['exec_time'] . "\n";
} else {
    print "Queue is empty\n";
}

print 'Done in ' . ($time_to_send = round(microtime(true) - ANGIE_SCRIPT_TIME, 5)) . " seconds\n";
die();
