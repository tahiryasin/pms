<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tasks\Utils\TaskDependenciesResolver;

use ITaskDependencies;
use ModelCollection;
use Task;
use TaskDependenciesCollection;
use TaskDependenciesSuggestionsCollection;

interface TaskDependenciesResolverInterface
{
    public function getDependencies(ITaskDependencies $task): TaskDependenciesCollection;

    public function getDependencySuggestions(ITaskDependencies $task): TaskDependenciesSuggestionsCollection;

    public function getProjectDependenciesCollection(int $project_id): ModelCollection;

    public function getProjectDependencies(int $project_id): array;

    public function countOpenDependencies(Task $task): array;

    public function isTaskBetweenScheduledDependencies(Task $task): bool;
}
