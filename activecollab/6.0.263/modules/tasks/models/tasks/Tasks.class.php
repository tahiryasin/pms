<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskEvents\TaskCompletedEvent;
use ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskEvents\TaskCreatedEvent;
use ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskEvents\TaskListChangedEvent;
use ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskEvents\TaskReopenedEvent;
use ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskEvents\TaskReorderedEvent;
use ActiveCollab\Module\Tasks\Events\DataObjectLifeCycleEvents\TaskEvents\TaskUpdatedEvent;

class Tasks extends BaseTasks
{
    use IProjectElementsImplementation;

    // Sharing context
    const SHARING_CONTEXT = 'request';

    // default orders
    const ORDER_ANY = 'ISNULL(completed_on) DESC, position ASC, is_important DESC, created_on, integer_field_1';
    const ORDER_OPEN = 'ISNULL(position) ASC, position, is_important DESC, created_on, integer_field_1';
    const ORDER_COMPLETED = 'ISNULL(position) ASC, position, is_important DESC, created_on, integer_field_1';

    // Task appearance administration options
    const TASK_OPTION_SUBTASKS = 'subtasks';
    const TASK_OPTION_TRACKING = 'tracking';
    const TASK_OPTION_EXPENSES = 'expenses';

    const TASKS_FOR_SCREEN_PAGINATION = 5000;

    /**
     * @var array
     */
    private static $preloaded_counts_by_project = false;
    private static $preloaded_counts_by_task_list = false;

    /**
     * Return new collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if (str_starts_with($collection_name, 'project_tasks')) {
            return self::prepareProjectTasksCollectionByProject($collection_name, $user);
        } elseif (str_starts_with($collection_name, 'for_screen')) {
            return self::prepareProjectTasksRawCollection($collection_name, $user);
        }  elseif (str_starts_with($collection_name, 'workload')) {
            return self::prepareWorkloadTasksCollection($collection_name, $user);
        } else {
            if (str_starts_with($collection_name, 'active_tasks_in_project') || str_starts_with($collection_name, 'all_tasks_in_project')) {
                self::prepareAssignmentsCollectionByProject($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'archived_tasks_in_project')) {
                self::prepareArchivedAssignmentsCollectionByProject($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'archived_tasks_in_task_list')) {
                self::prepareArchivedAssignmentsCollectionByTaskList($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'open_tasks_assigned_to_user')) {
                self::prepareAssignmentsCollectionByUser($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'open_tasks_assigned_to_team')) {
                self::prepareAssignmentsCollectionByTeam($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'assignments_as_calendar_events')) {
                self::prepareCalendarEventsCollection($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'tasks_dependencies_suggestion')) {
                self::prepareTaskDependenicesSuggestionCollectionByProjectAndTask($collection, $collection_name, $user);
            }  elseif (str_starts_with($collection_name, 'task_dependencies')) {
                self::prepareTaskDependenicesCollectionByProjectAndTask($collection, $collection_name, $user);
            } elseif (str_starts_with($collection_name, 'for_screen')) {
                self::prepareProjectTasksRawCollection($collection_name, $user);
            } else {
                throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
            }
        }

        return $collection;
    }

    private static function prepareProjectTasksRawCollection($collection_name, User $user)
    {
        $bits = explode('_', $collection_name);

        $task_status_filter = array_pop($bits);
        array_pop($bits); // _status_
        $project_id = array_pop($bits);

        /** @var Project $project */
        if ($project = DataObjectPool::get(Project::class, $project_id)) {
            return (new ProjectTasksRawCollection($collection_name))
                ->setProject($project)
                ->setTaskStatusFilter($task_status_filter)
                ->setWhosAsking($user);
        } else {
            throw new InvalidParamError('collection_name', $collection_name, 'Project ID expected in collection name');
        }
    }

    private static function prepareWorkloadTasksCollection($collection_name, User $user)
    {
        $bits = explode('_', $collection_name);

        $end_date = new DateValue(array_pop($bits));
        array_pop($bits); // _end_
        $start_date = new DateValue(array_pop($bits));
        array_pop($bits); // _start_
        $filter = array_pop($bits);

        if ($start_date instanceof DateValue && $end_date instanceof DateValue) {
            return (new WorkloadTasksCollection($collection_name))
                ->setFilter($filter)
                ->setStartDate($start_date)
                ->setEndDate($end_date)
                ->setWhosAsking($user);
        } else {
            throw new InvalidParamError(
                'collection_name',
                $collection_name,
                'Start and End dates are not valid dates'
            );
        }
    }

    /**
     * Prepare tasks collection by filtered by project ID.
     *
     * @param  string                 $collection_name
     * @param  User                   $user
     * @return ProjectTasksCollection
     * @throws InvalidParamError
     */
    private static function prepareProjectTasksCollectionByProject($collection_name, User $user)
    {
        $bits = explode('_', $collection_name);

        /** @var Project $project */
        if ($project = DataObjectPool::get(Project::class, array_pop($bits))) {
            return (new ProjectTasksCollection($collection_name))->setProject($project)->setWhosAsking($user);
        } else {
            throw new InvalidParamError('collection_name', $collection_name, 'Project ID expected in collection name');
        }
    }

    /**
     * Prepare tasks collection by filtered by project ID.
     *
     * @param  ModelCollection           $collection
     * @param  string                    $collection_name
     * @param  User                      $user
     * @throws ImpossibleCollectionError
     * @throws InvalidParamError
     */
    private static function prepareAssignmentsCollectionByProject(ModelCollection &$collection, $collection_name, $user)
    {
        $bits = explode('_', $collection_name);
        $project_id = array_pop($bits);

        $project = DataObjectPool::get('Project', $project_id);

        if ($project instanceof Project) {
            $collection->setOrderBy('position');

            if (str_starts_with($collection_name, 'active_tasks_in_project')) {
                if ($user instanceof Client) {
                    $collection->setConditions('project_id = ? AND completed_on IS NULL AND is_trashed = ? AND is_hidden_from_clients = ?', $project->getId(), false, false);
                } else {
                    $collection->setConditions('project_id = ? AND completed_on IS NULL AND is_trashed = ?', $project->getId(), false);
                }
            } elseif (str_starts_with($collection_name, 'all_tasks_in_project')) {
                if ($user instanceof Client) {
                    $collection->setConditions('project_id = ? AND is_hidden_from_clients = ?', $project->getId(), false);
                } else {
                    $collection->setConditions('project_id = ?', $project->getId(), false);
                }
            } else {
                throw new InvalidParamError('collection_name', $collection_name);
            }
        } else {
            throw new ImpossibleCollectionError("Project #{$project_id} not found");
        }
    }

    /**
     * Prepare tasks collection by filtered by project ID.
     *
     * @param  ModelCollection           $collection
     * @param  string                    $collection_name
     * @param  User                      $user
     * @throws ImpossibleCollectionError
     * @throws InvalidParamError
     */
    private static function prepareArchivedAssignmentsCollectionByProject(ModelCollection &$collection, $collection_name, $user)
    {
        $bits = explode('_', $collection_name);

        $page = (int) array_pop($bits);
        array_pop($bits); // _page_

        $project = DataObjectPool::get('Project', array_pop($bits));

        if ($project instanceof Project) {
            if ($user instanceof Client) {
                $collection->setConditions('project_id = ? AND completed_on IS NOT NULL AND is_trashed = ? AND is_hidden_from_clients = ?', $project->getId(), false, false);
            } else {
                $collection->setConditions('project_id = ? AND completed_on IS NOT NULL AND is_trashed = ?', $project->getId(), false);
            }

            $collection->setPagination($page, 30);
            $collection->setOrderBy('completed_on DESC');
        } else {
            throw new ImpossibleCollectionError('Project not found');
        }
    }

    /**
     * Prepare tasks collection by filtered by task list ID.
     *
     * @param  ModelCollection           $collection
     * @param  string                    $collection_name
     * @param  User                      $user
     * @throws ImpossibleCollectionError
     * @throws InvalidParamError
     */
    public static function prepareArchivedAssignmentsCollectionByTaskList(ModelCollection &$collection, $collection_name, $user)
    {
        $bits = explode('_', $collection_name);

        $page = (int) array_pop($bits);
        array_pop($bits); // _page_

        $task_list = DataObjectPool::get('TaskList', array_pop($bits));

        if ($task_list instanceof TaskList) {
            if ($user instanceof Client) {
                $collection->setConditions('task_list_id = ? AND completed_on IS NOT NULL AND is_trashed = ? AND is_hidden_from_clients = ?', $task_list->getId(), false, false);
            } else {
                $collection->setConditions('task_list_id = ? AND completed_on IS NOT NULL AND is_trashed = ?', $task_list->getId(), false);
            }

            $collection->setPagination($page, 100);
            $collection->setOrderBy('completed_on DESC');
        } else {
            throw new ImpossibleCollectionError('Project not found');
        }
    }

    /**
     * Prepare tasks collection filtered by user.
     *
     * @param  ModelCollection           $collection
     * @param  string                    $collection_name
     * @param  User                      $user
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    private static function prepareAssignmentsCollectionByUser(ModelCollection &$collection, $collection_name, $user)
    {
        $bits = explode('_', $collection_name);
        $assignee_id = array_pop($bits);

        $assignee = DataObjectPool::get('User', $assignee_id);

        if ($assignee instanceof User) {
            $project_ids = Users::prepareProjectIdsFilterByUser($user);

            $collection->setOrderBy('position');

            if ($project_ids === true) {
                $collection->setConditions('assignee_id = ? AND completed_on IS NULL AND is_trashed = ?', $assignee->getId(), false);
            } else {
                $collection->setConditions('assignee_id = ? AND project_id IN (?) AND completed_on IS NULL AND is_trashed = ?', $assignee->getId(), $project_ids, false);
            }
        } else {
            throw new ImpossibleCollectionError("Assignee #{$assignee_id} not found");
        }
    }

    /**
     * Prpare task collection filtered by team.
     *
     * @param  ModelCollection           $collection
     * @param  string                    $collection_name
     * @param  User                      $user
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    private static function prepareAssignmentsCollectionByTeam(ModelCollection &$collection, $collection_name, $user)
    {
        $bits = explode('_', $collection_name);
        $team_id = array_pop($bits);

        $team = DataObjectPool::get('Team', $team_id);

        if ($team instanceof Team && $team->countMembers()) {
            $project_ids = Users::prepareProjectIdsFilterByUser($user);

            if ($project_ids === true) {
                $collection->setConditions('assignee_id IN (?) AND completed_on IS NULL AND is_trashed = ?', $team->getMemberIds(), false);
            } else {
                $collection->setConditions('assignee_id IN (?) AND project_id IN (?) AND completed_on IS NULL AND is_trashed = ?', $team->getMemberIds(), $project_ids, false);
            }
        } else {
            throw new ImpossibleCollectionError("Team #{$team_id} not found or team has no members");
        }
    }

    // ---------------------------------------------------
    //  Operations
    // ---------------------------------------------------

    /**
     * Prepare calendar events collection.
     *
     * @param  ModelCollection           $collection
     * @param                            $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    private static function prepareCalendarEventsCollection(ModelCollection &$collection, $collection_name, $user)
    {
        $bits = explode('_', $collection_name);

        $to = array_pop($bits);
        $from = array_pop($bits);

        $parts = [DB::prepare('is_trashed = ? AND start_on IS NOT NULL AND due_on IS NOT NULL AND ((start_on BETWEEN ? AND ?) OR (due_on BETWEEN ? AND ?) OR (start_on < ? AND due_on > ?))', false, $from, $to, $from, $to, $from, $to)];

        if ($user instanceof Client) {
            $parts[] = DB::prepare('is_hidden_from_clients = ?', false);
        }

        $additional_conditions = implode(' AND ', $parts);

        // everything in all projects
        if (str_starts_with($collection_name, 'assignments_as_calendar_events_everything_in_all_projects')) {
            if ($user->isPowerUser()) {
                $collection->setConditions($additional_conditions);
            } else {
                throw new ImpossibleCollectionError('Only project managers can see everything in all projects');
            }

            // everything in my projects
        } elseif (str_starts_with($collection_name, 'assignments_as_calendar_events_everything_in_my_projects')) {
            $project_ids = Projects::findIdsByUser($user, false, DB::prepare('is_trashed = ?', false));

            if ($project_ids && is_foreachable($project_ids)) {
                $collection->setConditions("project_id IN (?) AND $additional_conditions", $project_ids);
            } else {
                throw new ImpossibleCollectionError('User not involved in any of the projects');
            }

            // only my assignments
        } elseif (str_starts_with($collection_name, 'assignments_as_calendar_events_only_my_assignments')) {
            if ($user->isPowerUser() || $user->isMember() || $user->isSubcontractor()) {
                $project_ids = Projects::findIdsByUser($user, false, DB::prepare('is_trashed = ?', false));

                if ($project_ids && is_foreachable($project_ids)) {
                    $collection->setConditions("project_id IN (?) AND assignee_id = ? AND $additional_conditions", $project_ids, $user->getId());
                } else {
                    throw new ImpossibleCollectionError('User not involved in any of the projects');
                }
            } else {
                throw new ImpossibleCollectionError('User need to be Member or Subcontractor');
            }

            // assignments for specified user
        } elseif (str_starts_with($collection_name, 'assignments_as_calendar_events')) {
            $for_id = array_pop($bits);

            if ($user->isPowerUser()) {
                $for = DataObjectPool::get('User', $for_id);

                if ($for instanceof User) {
                    $project_ids = Projects::findIdsByUser($for, false, DB::prepare('is_trashed = ?', false));

                    if ($project_ids && is_foreachable($project_ids)) {
                        $collection->setConditions("project_id IN (?) AND assignee_id = ? AND $additional_conditions", $project_ids, $for->getId());
                    } else {
                        throw new ImpossibleCollectionError('User not involved in any of the projects');
                    }
                } else {
                    throw new ImpossibleCollectionError("User #{$for_id} not found");
                }
            } else {
                throw new ImpossibleCollectionError('Only project managers can see assignments for specified user');
            }

            // invalid collection name
        } else {
            throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
        }

        return $collection;
    }

    private static function prepareTaskDependenicesSuggestionCollectionByProjectAndTask(
        ModelCollection &$collection,
        $collection_name,
        User $user
    )
    {
        $bits = explode('_', $collection_name);

        $task_id = (int) array_pop($bits);
        array_pop($bits); // _task_
        $project_id = array_pop($bits);

        $sugestion_task_ids = DB::executeFirstColumn(
            'SELECT t.id
            FROM tasks t
            WHERE t.id != ? AND t.project_id = ? AND t.id NOT IN
            (
              SELECT td1.child_id
              FROM task_dependencies td1
              WHERE td1.parent_id = ?
            ) AND t.id NOT IN
            (
              SELECT td2.parent_id
              FROM task_dependencies td2
              WHERE td2.child_id = ?
            )',
            $task_id,
            $project_id,
            $task_id,
            $task_id
        );

        if ($user->isClient()) {
            $collection->setConditions(
                'id IN (?) AND completed_on IS NULL AND is_trashed = ? AND is_hidden_from_clients = ?',
                $sugestion_task_ids,
                false,
                false
            );
        } else {
            $collection->setConditions(
                'id IN (?) AND completed_on IS NULL AND is_trashed = ?',
                $sugestion_task_ids,
                false
            );
        }
    }

    private static function prepareTaskDependenicesCollectionByProjectAndTask(
        ModelCollection &$collection,
        $collection_name,
        User $user
    )
    {
        $bits = explode('_', $collection_name);

        $task_id = (int) array_pop($bits);
        array_pop($bits); // _task_
        $dependency_type = array_pop($bits);

        if ($dependency_type === 'parents') {
            $task_ids = DB::executeFirstColumn(
                'SELECT parent_id FROM task_dependencies WHERE child_id = ?',
                $task_id
            );
        } else {
            $task_ids = DB::executeFirstColumn(
                'SELECT child_id FROM task_dependencies WHERE parent_id = ?',
                $task_id
            );
        }

        if ($user->isClient()) {
            $collection->setConditions('id IN (?) AND is_trashed = ? AND is_hidden_from_clients = ?', $task_ids, false, false);
        } else {
            $collection->setConditions('id IN (?) AND is_trashed = ?', $task_ids, false);
        }
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $notify_subscribers = array_var($attributes, 'notify_subscribers', true, true);

        $task = parent::create($attributes, $save, false);

        if ($task instanceof Task && $task->isLoaded()) {
            /** @var Task $task */
            $task = self::autoSubscribeProjectLeader($task);

            if ($notify_subscribers && empty($attributes['created_from_recurring_task_id'])) {
                AngieApplication::notifications()
                    ->notifyAbout(
                        'tasks/new_task',
                        $task,
                        $task->getCreatedBy()
                    )->sendToSubscribers();
            }
        }

        if ($task->isLoaded()) {
            DataObjectPool::announce(new TaskCreatedEvent($task));
        }

        return $task;
    }

    public static function reorderToTaskList(Task $task, TaskList $task_list)
    {
        $task->setTaskListId($task_list->getId());
        $task->setPosition(self::findNextPositionInTaskList($task_list));
        $task->save();

        self::clearCacheFor($task->getId());

        $task_list->touch();
        $task = DataObjectPool::reload(Task::class, $task->getId());

        DataObjectPool::announce(new TaskUpdatedEvent($task));

        return DB::executeFirstColumn(
            'SELECT id FROM tasks WHERE task_list_id = ? ORDER BY position ASC',
            $task_list->getId()
        );
    }

    /**
     * Reorder tasks.
     * Reverse, if true put source task after target task and vice versa.
     *
     * @param  Task                 $source
     * @param  Task                 $target
     * @param  bool                 $before
     * @return array
     * @throws Exception
     * @throws InvalidInstanceError
     */
    public static function reorder(Task $source, Task $target, $before = false)
    {
        $ordered_tasks = [];
        $affected_tasks = [$source->getId()];
        $target_task_list = $target->getTaskList();
        $task_list_changed = $source->getTaskListId() != $target_task_list->getId();

        DB::transact(
            function () use ($source, $target_task_list, $target, $task_list_changed, $before, &$affected_tasks, &$ordered_tasks) {
                $conditions = [
                    DB::prepare('id != ?', $source->getId()),
                    DB::prepare('task_list_id = ?', $target_task_list->getId()),
                ];
                $conditions = implode(' AND ', $conditions);
                $query = "SELECT id FROM tasks WHERE $conditions ORDER BY position ASC";

                $position = 1;
                $when_then_cases = '';

                if ($tasks_after_position = DB::executeFirstColumn($query)) {
                    $position = count($tasks_after_position) + 1;
                    $position_counter = 1;
                    $shift_next_positon = false;

                    foreach ($tasks_after_position as $tasks_after_position_id) {
                        if ($shift_next_positon) {
                            $position = $position_counter;
                            ++$position_counter;
                            $shift_next_positon = false;
                        }

                        if ($tasks_after_position_id == $target->getId()) {
                            if ($before) {
                                $position = $position_counter;
                                ++$position_counter;
                            } else {
                                $shift_next_positon = true;
                            }
                        }

                        $when_then_cases .= "WHEN {$tasks_after_position_id} THEN {$position_counter} ";

                        $affected_tasks[] = $tasks_after_position_id;
                        $ordered_tasks[$position_counter] = $tasks_after_position_id;

                        ++$position_counter;
                    }
                }

                $when_then_cases .= "WHEN {$source->getId()} THEN $position ";
                $ordered_tasks[$position] = $source->getId();

                DB::execute(
                    "UPDATE `tasks`
                        SET `updated_on` = UTC_TIMESTAMP(), `position` = (CASE `id` $when_then_cases END)
                        WHERE `id` IN (?)",
                    $affected_tasks
                );

                if ($task_list_changed) {
                    $source->setTaskList($target_task_list);
                    $source->save();

                    DataObjectPool::announce(new TaskUpdatedEvent($source));
                }
            }
        );

        self::clearCacheFor($affected_tasks);

        $target_task_list->touch();
        $source = DataObjectPool::reload(Task::class, $source->getId());

        DataObjectPool::announce(new TaskReorderedEvent($source));

        ksort($ordered_tasks);

        return array_values($ordered_tasks);
    }

    /**
     * Batch update tasks.
     *
     * @param  array   $task_ids
     * @param  array   $attributes
     * @param  User    $by
     * @param  Project $project
     * @return Task[]
     */
    public static function batchUpdate(array $task_ids, array $attributes, User $by, Project $project)
    {
        $result = [];

        if (empty($attributes)) {
            return $result;
        }

        $active_task_ids = DB::executeFirstColumn(
            'SELECT id FROM tasks WHERE id IN (?) AND is_trashed = ?',
            $task_ids,
            false
        );

        $tasks = self::findByIds($active_task_ids);

        if ($tasks) {
            DB::transact(
                function () use ($tasks, $attributes, $by, $project, &$result) {
                    $complete_all = isset($attributes['complete_all']) && $attributes['complete_all'];
                    $trash_all = isset($attributes['trash_all']) && $attributes['trash_all'];

                    unset($attributes['complete_all']);
                    unset($attributes['trash_all']);

                    $task_list = !empty($attributes['task_list_id'])
                        ? DataObjectPool::get(TaskList::class, $attributes['task_list_id'])
                        : null;

                    if ($task_list instanceof TaskList && $task_list->getProjectId() == $project->getId()) {
                        $attributes['task_list_id'] = $task_list->getId();
                    }

                    /** @var Task $task */
                    foreach ($tasks as $task) {
                        if ($task->canEdit($by)) {
                            if (self::shouldSetStartsOnToDueOn($task, $attributes)) {
                                $task->setStartOn($attributes['due_on']);
                            }

                            self::update($task, $attributes);

                            if ($complete_all && $task->isOpen()) {
                                $task->complete($by);
                            }
                        }

                        if ($trash_all && $task->canDelete($by)) {
                            self::scrap($task);
                        }

                        if ($task->canView($by)) {
                            $result[] = $task;
                        }
                    }
                },
                'Batch update tasks'
            );
        }

        self::clearCache();

        return $result;
    }

    /**
     * Bulk update tasks.
     *
     * @param  array $task_ids
     * @param  array $tasks_attributes
     * @param  User  $by
     * @return array
     */
    public static function bulkUpdate(array $task_ids, array $tasks_attributes, User $by)
    {
        $result = [];

        $active_task_ids = DB::executeFirstColumn(
            'SELECT id FROM tasks WHERE id IN (?) AND is_trashed = ?',
            $task_ids,
            false
        );

        $tasks = self::findByIds($active_task_ids);

        if (is_foreachable($tasks)) {
            $map_task_attributes_by_ids = [];

            if (is_foreachable($tasks_attributes)) {
                foreach ($tasks_attributes as $value) {
                    if (
                        array_key_exists('id', $value) && !empty($value['id'])
                        && array_key_exists('due_on', $value) && !empty($value['due_on'])
                    ) {
                        $map_task_attributes_by_ids[$value['id']] = $value;
                    }
                }
            }

            DB::transact(
                function () use ($tasks, $map_task_attributes_by_ids, $by, &$result) {
                    /** @var Task $task */
                    foreach ($tasks as $task) {
                        if (isset($map_task_attributes_by_ids[$task->getId()]) && $task->canEdit($by)) {
                            $attributes = $map_task_attributes_by_ids[$task->getId()];

                            if (self::shouldSetStartsOnToDueOn($task, $attributes)) {
                                $attributes['start_on'] = $attributes['due_on'];
                            }

                            self::update($task, $attributes);
                        }

                        if ($task->canView($by)) {
                            $result[] = $task;
                        }
                    }
                },
                'Bulk update tasks'
            );
        }

        self::clearCache();

        return $result;
    }

    private static function shouldSetStartsOnToDueOn(Task $task, array $attributes)
    {
        return (!empty($task->getStartOn()) && !empty($task->getDueOn()))
            && (isset($attributes['due_on']) && (!isset($attributes['start_on']) || empty($attributes['start_on'])))
            && ($task->getStartOn()->getTimestamp() == $task->getDueOn()->getTimestamp());
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Update an instance.
     *
     * @param  DataObject|Task $instance
     * @param  array           $attributes
     * @param  bool            $save
     * @return DataObject
     * @throws Exception
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        try {
            DB::beginWork('Update a task @ ' . __CLASS__);

            if ((array_key_exists('job_type_id', $attributes) && empty($attributes['job_type_id'])) || (array_key_exists('estimate', $attributes) && empty($attributes['estimate']))) {
                $attributes['job_type_id'] = 0;
                $attributes['estimate'] = 0;
            }

            if (!empty($attributes['is_hidden_from_clients'])) {
                if ($subtasks = $instance->getSubtasks(true)) {
                    /** @var Subtask $subtask */
                    foreach ($subtasks as $subtask) {
                        if ($subtask->getAssignee() instanceof Client) {
                            Subtasks::update($subtask, ['assignee_id' => 0]);
                        }
                    }
                }

                if ($instance->getAssignee() instanceof Client) {
                    $attributes['assignee_id'] = 0;
                }
            }

            $task_list_changed = array_key_exists('task_list_id', $attributes) && $instance->getTaskListId() != $attributes['task_list_id'];

            if ($task_list_changed) {
                $attributes['position'] = self::findNextPositionInTaskList($attributes['task_list_id']);
            }

            $assignee_id = $instance->getAssigneeId();

            // If assignee is changed set task fake assignee and email to null. Used for sample project!
            $task_assignee_changed = array_key_exists('assignee_id', $attributes)
                && ($assignee_id != $attributes['assignee_id'] || $attributes['assignee_id'] === null);

            if ($task_assignee_changed) {
                $attributes['fake_assignee_name'] = null;
                $attributes['fake_assignee_email'] = null;
            }

            $task = parent::update($instance, $attributes, $save);

            DB::commit('Task updated @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to update a task @ ' . __CLASS__);
            throw $e;
        }

        if ($task instanceof Task) {
            $assignee = $task->getAssignee();

            if ($assignee instanceof User && $assignee->getId() != $assignee_id && $assignee->isActive()) {
                $task->subscribe($assignee, true);

                AngieApplication::notifications()
                    ->notifyAbout('tasks/task_reassigned', $task, $task->getUpdatedBy())
                    ->sendToUsers($assignee);
            }
        }

        self::announceTaskUpdate($task, $task_list_changed, $attributes);

        return $task;
    }

    private static function announceTaskUpdate(Task $task, bool $task_list_changed, array $attributes): void
    {
        if ($task_list_changed) {
            DataObjectPool::announce(new TaskListChangedEvent($task));
        } elseif (self::isTaskCompletedStateChanged($attributes)) {
            if ($task->isCompleted()) {
                DataObjectPool::announce(new TaskCompletedEvent($task));
            } else {
                DataObjectPool::announce(new TaskReopenedEvent($task));
            }
        } else {
            DataObjectPool::announce(new TaskUpdatedEvent($task));
        }
    }

    private static function isTaskCompletedStateChanged(array $attributes): bool
    {
        foreach (['completed_by_id', 'completed_by_name', 'completed_by_email', 'completed_on'] as $attribute) {
            if (array_key_exists($attribute, $attributes)) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------------------
    //  Utility
    // ---------------------------------------------------

    /**
     * Returns true if $user can create a new task in $project.
     *
     * @param  User|Client $user
     * @param  Project     $project
     * @return bool
     */
    public static function canAdd(User $user, Project $project)
    {
        // no new tasks allowed in trashed projects
        if ($project instanceof ITrash && $project->getIsTrashed()) {
            return false;
        }

        // no new tasks allowed in completed projects
        if ($project instanceof IComplete && $project->isCompleted()) {
            return false;
        }

        // clients aren't allowed to add tasks
        if ($user->isClient() && !$user->canManageTasks()) {
            return false;
        }

        return $user instanceof User && ($user->isOwner() || $project->isMember($user));
    }

    // ---------------------------------------------------
    //  Finders
    // ---------------------------------------------------

    public static function whatIsWorthRemembering(): array
    {
        return [
            'project_id',
            'name',
            'task_list_id',
            'assignee_id',
            'estimate',
            'job_type_id',
            'start_on',
            'due_on',
            'is_important',
            'completed_on',
            'is_trashed',
        ];
    }

    /**
     * Preload counts for the given projects (to bring the number of queries down).
     *
     * @param int[] $project_ids
     * @param bool  $force_refresh
     */
    public static function preloadCountByProject(array $project_ids, $force_refresh = false)
    {
        if (self::$preloaded_counts_by_project === false || $force_refresh) {
            self::$preloaded_counts_by_project = [];

            if ($rows = DB::execute("SELECT project_id, COUNT('id') AS 'row_count' FROM tasks WHERE completed_on IS NULL AND is_trashed = ? AND project_id IN (?) GROUP BY project_id", false, $project_ids)) {
                foreach ($rows as $row) {
                    self::$preloaded_counts_by_project[$row['project_id']] = (int) $row['row_count'];
                }
            }
        }
    }

    /**
     * Preload task counters by task lists.
     *
     * @param array $task_list_ids
     */
    public static function preloadCountByTaskList(array $task_list_ids)
    {
        if (self::$preloaded_counts_by_task_list === false) {
            self::$preloaded_counts_by_task_list = array_fill_keys($task_list_ids, ['open' => 0, 'completed' => 0]);

            if ($rows = DB::execute("SELECT task_list_id, COUNT('id') AS 'row_count' FROM tasks WHERE completed_on IS NULL AND is_trashed = ? AND task_list_id IN (?) GROUP BY task_list_id", false, $task_list_ids)) {
                foreach ($rows as $row) {
                    self::$preloaded_counts_by_task_list[$row['task_list_id']]['open'] = (int) $row['row_count'];
                }
            }

            if ($rows = DB::execute("SELECT task_list_id, COUNT('id') AS 'row_count' FROM tasks WHERE completed_on IS NOT NULL AND is_trashed = ? AND task_list_id IN (?) GROUP BY task_list_id", false, $task_list_ids)) {
                foreach ($rows as $row) {
                    self::$preloaded_counts_by_task_list[$row['task_list_id']]['completed'] = (int) $row['row_count'];
                }
            }
        }
    }

    /**
     * Reset manager state (between tests for example).
     */
    public static function resetState()
    {
        self::$preloaded_counts_by_project = [];
        self::$preloaded_counts_by_task_list = [];
    }

    /**
     * Return number of open tasks in $project.
     *
     * @param  Project $project
     * @param  bool    $use_cache
     * @return int
     */
    public static function countOpenByProject(Project $project, bool $use_cache = true)
    {
        if ($use_cache && self::$preloaded_counts_by_project !== false) {
            return isset(self::$preloaded_counts_by_project[$project->getId()])
                ? self::$preloaded_counts_by_project[$project->getId()]
                : 0;
        } else {
            return static::count(
                [
                    'project_id = ? AND completed_on IS NULL AND is_trashed = ?',
                    $project->getId(),
                    false,
                ]
            );
        }
    }

    /**
     * Return number of completed tasks in a project.
     *
     * @param  Project $project
     * @return int
     */
    public static function countCompletedByProject(Project $project)
    {
        return static::count(['project_id = ? AND completed_on IS NOT NULL AND is_trashed = ?', $project->getId(), false]);
    }

    /**
     * Return number of open tasks in $task_list.
     *
     * @param  TaskList $task_list
     * @return int
     */
    public static function countOpenByTaskList(TaskList $task_list)
    {
        return AngieApplication::cache()->getByObject($task_list, 'open_tasks_count', function () use ($task_list) {
            if (self::$preloaded_counts_by_task_list !== false) {
                return isset(self::$preloaded_counts_by_task_list[$task_list->getId()]) ? self::$preloaded_counts_by_task_list[$task_list->getId()]['open'] : 0;
            } else {
                return static::count(['task_list_id = ? AND completed_on IS NULL AND is_trashed = ?', $task_list->getId(), false, false]);
            }
        });
    }

    /**
     * Return number of open tasks in $task_list.
     *
     * @param  TaskList $task_list
     * @return int
     */
    public static function countCompletedByTaskList(TaskList $task_list)
    {
        return AngieApplication::cache()->getByObject($task_list, 'completed_tasks_count', function () use ($task_list) {
            if (self::$preloaded_counts_by_task_list !== false) {
                return isset(self::$preloaded_counts_by_task_list[$task_list->getId()]) ? self::$preloaded_counts_by_task_list[$task_list->getId()]['completed'] : 0;
            } else {
                return static::count(['task_list_id = ? AND completed_on IS NOT NULL AND is_trashed = ?', $task_list->getId(), false, false]);
            }
        });
    }

    /**
     * Return number of tasks that use a given job type.
     *
     * @param  JobType $job_type
     * @return int
     */
    public static function countByJobType(JobType $job_type)
    {
        return self::count(['job_type_id = ?', $job_type->getId()]);
    }

    /**
     * Return number of unscheduled tasks for the given projects, indexed by project ID.
     *
     * @param  array $project_ids
     * @return array
     */
    public static function countUnscheduledInProjects(array $project_ids)
    {
        $result = array_fill_keys($project_ids, 0);

        if ($rows = DB::execute("SELECT project_id, COUNT(id) AS 'row_count' FROM tasks WHERE project_id IN (?) AND start_on IS NULL AND due_on IS NULL AND completed_on IS NULL AND is_trashed = ? GROUP BY project_id", $project_ids, false)) {
            foreach ($rows as $row) {
                $result[$row['project_id']] = $row['row_count'];
            }
        }

        return $result;
    }

    /**
     * Return task by task number.
     *
     * @param  Project              $project
     * @param  int                  $number
     * @return Task|DataObject|null
     */
    public static function findByTaskNumber(Project $project, $number)
    {
        return self::find(
            [
                'conditions' => ['project_id = ? AND task_number = ?', $project->getId(), $number],
                'one' => true,
            ]
        );
    }

    /**
     * Return task by discussion id - if is converted from discussion.
     *
     * @param  int                  $discussion_id
     * @return Task|DataObject|null
     */
    public static function findByDiscussionId($discussion_id)
    {
        return self::find(
            [
                'conditions' => ['created_from_discussion_id = ? ', $discussion_id],
                'one' => true,
            ]
        );
    }

    /**
     * Return next field value in a given project.
     *
     * @param  Project|int $project
     * @param  string      $field
     * @return int
     */
    public static function findNextFieldValueByProject($project, $field)
    {
        return DB::executeFirstCell(
            "SELECT MAX($field) FROM tasks WHERE project_id = ?",
            $project instanceof Project ? $project->getId() : (int) $project
        ) + 1;
    }

    /**
     * Return next position in a given task list.
     *
     * @param  TaskList|int $task_list
     * @return int
     */
    public static function findNextPositionInTaskList($task_list)
    {
        return DB::executeFirstCell(
            'SELECT MAX(position) FROM tasks WHERE task_list_id = ?',
            $task_list instanceof TaskList ? $task_list->getId() : (int) $task_list
        ) + 1;
    }

    /**
     * Revoke assignee on all tasks where $user is assigned.
     *
     * @param  User                         $user
     * @param  User                         $by
     * @throws InsufficientPermissionsError
     */
    public static function revokeAssignee(User $user, User $by)
    {
        if (!$user->canChangeRole($by, false)) {
            throw new InsufficientPermissionsError();
        }

        if ($tasks_assigned_to = self::findBy('assignee_id', $user->getId())) {
            foreach ($tasks_assigned_to as $task) {
                $task->setAssignee(null, $by);
            }
        }
    }

    /**
     * Update task instance and reschedule dependencies.
     *
     * @param  DataObject      $instance
     * @param  array           $attributes
     * @param  IUser           $by
     * @return DataObject|Task
     */
    public static function updateAndRescheduleDependencies(DataObject &$instance, array $attributes, IUser $by)
    {
        DB::transact(
            function () use ($attributes, $by, &$instance) {
                $due_on = null;

                if (isset($attributes['due_on']) && !empty($attributes['due_on'])) {
                    $due_on = DateValue::makeFromString((string) $attributes['due_on']);
                }

                if ($due_on instanceof DateValue) {
                    $start_on = null;

                    if (isset($attributes['start_on']) && !empty($attributes['start_on'])) {
                        $start_on = DateValue::makeFromString((string) $attributes['start_on']);
                    }

                    if (!($start_on instanceof DateValue)) {
                        $start_on = clone $due_on;
                    }

                    AngieApplication::skippableTaskDatesCorrector()->correctDates($instance, $start_on, $due_on);

                    $attributes['start_on'] = $start_on->toMySQL();
                    $attributes['due_on'] = $due_on->toMySQL();

                    $task_date_rescheduler = AngieApplication::taskDateRescheduler();

                    $simulation = $task_date_rescheduler->simulateReschedule($instance, $due_on);
                    $task_date_rescheduler->updateSimulationTaskDates($instance, $simulation, $by);
                }

                self::update($instance, $attributes);
            },
            'Edit tasks and reschedule dependencies'
        );

        return $instance;
    }
}
