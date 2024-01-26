<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Abstract assignments collection.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
abstract class AssignmentsCollection extends CompositeCollection
{
    use IWhosAsking;

    /**
     * Cached tag value.
     *
     * @var string
     */
    private $tag = false;

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
     * Return timestamp hash.
     *
     * @return string
     */
    public function getTimestampHash()
    {
        return sha1($this->getContextTimestamp() . ',' . $this->getProjectsTimestamp() . ',' . $this->getTasksCollections()->getTimestampHash('updated_on') . ',' . $this->getSubtasksCollection()->getTimestampHash('updated_on'));
    }

    /**
     * Return user or team timestamp.
     *
     * @return string
     */
    abstract public function getContextTimestamp();

    /**
     * @return string
     */
    private function getProjectsTimestamp()
    {
        return DB::executeFirstCell('SELECT MAX(updated_on) FROM projects');
    }

    /**
     * Return assigned tasks collection.
     *
     * @return ModelCollection
     */
    abstract protected function &getTasksCollections();

    /**
     * Return assigned subtasks collection.
     *
     * @return ModelCollection
     */
    abstract protected function &getSubtasksCollection();

    /**
     * Run the query and return DB result.
     *
     * @return DbResult|DataObject[]
     */
    public function execute()
    {
        $type_ids_map = ['Project' => [], 'TaskList' => [], 'Task' => []];

        /** @var Task[] $tasks */
        if ($tasks = $this->getTasksCollections()->execute()) {
            foreach ($tasks as $task) {
                if (!in_array($task->getProjectId(), $type_ids_map['Project'])) {
                    $type_ids_map['Project'][] = $task->getProjectId();
                }

                $task_list_id = $task->getTaskListId();

                if ($task_list_id && !in_array($task_list_id, $type_ids_map['TaskList'])) {
                    $type_ids_map['TaskList'][] = $task_list_id;
                }
            }
        }

        /** @var Subtask[] $subtasks */
        if ($subtasks = $this->getSubtasksCollection()->execute()) {
            foreach ($subtasks as $subtask) {
                if (!in_array($subtask->getProjectId(), $type_ids_map['Project'])) {
                    $type_ids_map['Project'][] = $subtask->getProjectId();
                }

                if (!in_array($subtask->getTaskId(), $type_ids_map['Task'])) {
                    $type_ids_map['Task'][] = $subtask->getTaskId();
                }
            }
        }

        foreach ($type_ids_map as $k => $v) {
            if (empty($v)) {
                unset($type_ids_map[$k]);
            }
        }

        // preload projects counts
        if (isset($type_ids_map[Project::class])) {
            Projects::preloadProjectElementCounts($type_ids_map[Project::class]);
        }
        // preload task lists counts
        if (isset($type_ids_map[TaskList::class])) {
            Tasks::preloadCountByTaskList($type_ids_map[TaskList::class]);
        }

        $result = [
            'tasks' => $tasks,
            'subtasks' => $subtasks,
            'related' => count($type_ids_map) ? DataObjectPool::getByTypeIdsMap($type_ids_map) : null,
        ];

        foreach ($result as $k => $v) {
            if (empty($v)) {
                $result[$k] = [];
            }
        }

        return $result;
    }

    /**
     * Return number of records that match conditions set by the collection.
     *
     * @return int
     */
    public function count()
    {
        return $this->getTasksCollections()->count() + $this->getSubtasksCollection()->count();
    }
}
