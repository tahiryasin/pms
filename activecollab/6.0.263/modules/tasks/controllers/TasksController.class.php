<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\Tasks\Utils\ProjectTasksFilter\TaskStatusFilter\TaskStatusFilterInterface;
use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\MovedResource\MovedResource;
use Angie\Http\Response\StatusResponse\StatusResponse;
use Angie\Http\Response\StatusResponse\StatusResponseInterface;

AngieApplication::useController('project', SystemModule::NAME);

/**
 * Tasks controller.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage controllers
 */
class TasksController extends ProjectController
{
    use MoveToProjectControllerAction;

    /**
     * Active task.
     *
     * @var Task
     */
    protected $active_task;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        if ($response = parent::__before($request, $user)) {
            return $response;
        }

        $this->active_task = DataObjectPool::get('Task', $request->getId('task_id'));

        if ($this->active_task instanceof Task) {
            if ($this->active_task->getProjectId() != $this->active_project->getId()) {
                $task_project = $this->active_task->getProject();

                if ($task_project instanceof Project && $task_project->isMember($user)) {
                    return new MovedResource($this->active_task->getViewUrl(), true);
                } else {
                    return Response::NOT_FOUND;
                }
            }
        } else {
            $this->active_task = new Task();
            $this->active_task->setProject($this->active_project);
        }

        return null;
    }

    /**
     * Show tasks index page.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function index(Request $request, User $user)
    {
        AccessLogs::logAccess($this->active_project, $user);

        return Tasks::prepareCollection('project_tasks_' . $this->active_project->getId(), $user);
    }

    public function for_screen(Request $request, User $user)
    {
        if (!($request->getAttribute('authenticated_with') instanceof UserSession)) {
            return Response::FORBIDDEN;
        }

        $collection_name = 'for_screen_'. $this->active_project->getId();
        $collection_name .= '_status_';
        $collection_name .= $request->get('status') ? $request->get('status') : 'all';

        return Tasks::prepareCollection($collection_name, $user);
    }

    public function filters(Request $request, User $user)
    {
        return [
            'status' => AngieApplication::getContainer()
                ->get(TaskStatusFilterInterface::class)
                ->getFilter(
                    $this->active_project->getId(),
                    $user->isClient()
                ),
        ];
    }

    /**
     * Show completed tasks (mobile devices only).
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function archive(Request $request, User $user)
    {
        AccessLogs::logAccess($this->active_project, $user);

        return Tasks::prepareCollection('archived_tasks_in_project_' . $this->active_project->getId() . '_page_' . $request->getPage(), $user);
    }

    /**
     * Show single task.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return int|Task
     */
    public function view(Request $request, User $user)
    {
        return $this->active_task->isLoaded() && $this->active_task->canView($user)
            ? AccessLogs::logAccess($this->active_task, $user)
            : Response::NOT_FOUND;
    }

    /**
     * Return time records for a task.
     *
     * @param  Request             $request
     * @param  User                $user
     * @return ModelCollection|int
     */
    public function time_records(Request $request, User $user)
    {
        if ($user instanceof Client && !$this->active_project->getIsClientReportingEnabled()) {
            return Response::NOT_FOUND;
        }

        return $this->active_task->isLoaded() && $this->active_task->canView($user)
            ? TimeRecords::prepareCollection('time_records_in_task_' . $this->active_task->getId() . '_page_' . $request->getPage(), $user)
            : Response::NOT_FOUND;
    }

    /**
     * Return expenses for a task.
     *
     * @param  Request             $request
     * @param  User                $user
     * @return ModelCollection|int
     */
    public function expenses(Request $request, User $user)
    {
        if ($user instanceof Client && !$this->active_project->getIsClientReportingEnabled()) {
            return Response::NOT_FOUND;
        }

        return $this->active_task->isLoaded() && $this->active_task->canView($user)
            ? Expenses::prepareCollection('expenses_in_task_' . $this->active_task->getId() . '_page_' . $request->getPage(), $user)
            : Response::NOT_FOUND;
    }

    /**
     * Create a new task.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return Task|int
     */
    public function add(Request $request, User $user)
    {
        if (Tasks::canAdd($user, $this->active_project)) {
            $post = $request->post();

            if ($post && is_array($post)) {
                $post['project_id'] = $this->active_project->getId();
            }

            return Tasks::create($post);
        }

        return Response::NOT_FOUND;
    }

    /**
     * Update existing task.
     *
     * @param  Request                                     $request
     * @param  User                                        $user
     * @return DataObject|Task|int|StatusResponseInterface
     */
    public function edit(Request $request, User $user)
    {
        if ($this->active_task->isLoaded() && $this->active_task->canEdit($user)) {
            $attributes = $request->put();

            if ($this->checkIsTaskBetweenScheduledDependencies($user, $attributes)) {
                return new StatusResponse(
                    Response::BAD_REQUEST,
                    '',
                    [
                        'message' => lang('Unable to remove the due date because of the parent/child dependency.'),
                        'type' => 'error', // because angular only handles error responses with type error or exception
                    ]
                );
            }

            return Tasks::updateAndRescheduleDependencies($this->active_task, $attributes, $user);
        }

        return Response::NOT_FOUND;
    }

    private function checkIsTaskBetweenScheduledDependencies(User $user, array $attributes)
    {
        $is_user_unsetting_task_due_on = $this->active_task->getDueOn()
            && array_key_exists('due_on', $attributes)
            && empty($attributes['due_on']);

        return $is_user_unsetting_task_due_on
            && AngieApplication::taskDependenciesResolver($user)->isTaskBetweenScheduledDependencies($this->active_task);
    }

    /**
     * Reorder tasks.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return array|int
     */
    public function reorder(Request $request, User $user)
    {
        $source_task = DataObjectPool::get(Task::class, $request->put('source_task_id'));

        if (!$source_task instanceof Task) {
            return Response::BAD_REQUEST;
        }

        if ($target_task_id = $request->put('target_task_id')) {
            $before = $request->put('before', false);
            $target_task = DataObjectPool::get(Task::class, $request->put('target_task_id'));

            if (!$target_task instanceof Task) {
                return Response::BAD_REQUEST;
            }

            return Tasks::reorder($source_task, $target_task, $before);
        } elseif ($target_task_list_id = $request->put('target_task_list_id')) {
            $target_task_list = DataObjectPool::get(TaskList::class, $target_task_list_id);

            if (!$target_task_list instanceof TaskList) {
                return Response::BAD_REQUEST;
            }

            return Tasks::reorderToTaskList($source_task, $target_task_list);
        } else {
            return Response::BAD_REQUEST;
        }
    }

    /**
     * Batch update.
     *
     * @param  Request    $request
     * @param  User       $user
     * @return Task[]|int
     */
    public function batch_update(Request $request, User $user)
    {
        $task_ids = $request->put('task_ids');
        $attributes = $request->put('attributes');

        return is_foreachable($task_ids) && is_foreachable($attributes)
            ? Tasks::batchUpdate($task_ids, $attributes, $user, $this->active_project)
            : Response::BAD_REQUEST;
    }

    /**
     * Move select task to trash.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_task->isLoaded() && $this->active_task->canDelete($user)
            ? Tasks::scrap($this->active_task)
            : Response::NOT_FOUND;
    }

    /**
     * @return Task
     */
    public function &getObjectToBeMoved()
    {
        return $this->active_task;
    }

    /**
     * Duplicate task.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return int|Task
     * @throws Exception
     */
    public function duplicate(Request $request, User $user)
    {
        $task_to_be_duplicated = $this->getObjectToBeMoved();

        if ($task_to_be_duplicated instanceof Task && $task_to_be_duplicated->isLoaded() && !$task_to_be_duplicated->getIsTrashed()) {
            $target_project = DataObjectPool::get(Project::class, $request->post('project_id'));

            if ($target_project instanceof Project) {
                if ($task_to_be_duplicated->canCopyToProject($user, $target_project)) {
                    return $task_to_be_duplicated->copyToProject(
                        $target_project,
                        $user,
                        function ($duplicated_task) use ($request) {
                            /* @var Task $duplicated_task */
                            $duplicated_task->setName($request->post('new_name'));
                        }
                    );
                }

                return Response::FORBIDDEN;
            }
        }

        return Response::NOT_FOUND;
    }
}
