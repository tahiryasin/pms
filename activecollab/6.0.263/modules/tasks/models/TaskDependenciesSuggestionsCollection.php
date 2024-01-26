<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class TaskDependenciesSuggestionsCollection extends CompositeCollection
{
    use IWhosAsking;

    /**
     * @var Task
     */
    private $task;

    /**
     * Cached tag value.
     *
     * @var string
     */
    private $tag = false;

    /**
     * @var ModelCollection
     */
    private $tasks_collection = false;
    private $task_lists_collection = false;

    /**
     * @var string
     */
    private $timestamp_hash = false;

    /**
     * @return string
     */
    public function getModelName()
    {
        return 'TaskDependencies';
    }

    /**
     * @param  Task  $task
     * @return $this
     */
    public function &setTask(Task $task)
    {
        $this->task = $task;

        return $this;
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
        if ($this->tag === false || empty($use_cache)) {
            $this->tag = $this->prepareTagFromBits($user->getEmail(), $this->getTimestampHash());
        }

        return $this->tag;
    }

    /**
     * @return string
     */
    private function getTimestampHash()
    {
        if ($this->timestamp_hash === false) {
            $this->timestamp_hash = sha1(
                $this->getTasksCollection()->getTimestampHash('updated_on') . '-' .
                $this->getTaskListsCollection()->getTimestampHash('updated_on')
            );
        }

        return $this->timestamp_hash;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $tasks_collection = $this->getTasksCollection() ? $this->getTasksCollection() : null;

        $filtered_tasks = [];

        if ($tasks_collection) {
            /** @var Task[] $tasks */
            $tasks = $tasks_collection->execute();

            if (!empty($tasks)) {
                foreach ($tasks as $task) {
                    $filtered_tasks[] = [
                        'id' => $task->getId(),
                        'name' => $task->getName(),
                        'task_number' => $task->getTaskNumber(),
                        'task_list_id' => $task->getTaskListId(),
                    ];
                }
            }
        }

        return [
            'tasks' => $filtered_tasks,
            'task_lists' => $this->getTaskListsCollection(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getTaskListsCollection()->count() + $this->getTasksCollection()->count();
    }

    private function getTasksCollection()
    {
        if ($this->tasks_collection === false) {
            $this->tasks_collection = Tasks::prepareCollection(
                'tasks_dependencies_suggestion_project_'  . $this->task->getProjectId() . '_task_' . $this->task->getId(),
                $this->getWhosAsking()
            );
        }

        return $this->tasks_collection;
    }

    private function getTaskListsCollection()
    {
        if ($this->task_lists_collection === false) {
            $this->task_lists_collection = TaskLists::prepareCollection(
                'tasks_dependencies_suggestion_project_'  . $this->task->getProjectId() . '_task_' . $this->task->getId(),
                $this->getWhosAsking()
            );
        }

        return $this->task_lists_collection;
    }
}
