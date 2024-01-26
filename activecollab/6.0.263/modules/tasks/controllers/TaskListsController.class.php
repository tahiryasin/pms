<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('project', SystemModule::NAME);

/**
 * Task lists controller.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
class TaskListsController extends ProjectController
{
    use MoveToProjectControllerAction;

    /**
     * Selected task list.
     *
     * @var TaskList
     */
    protected $active_task_list;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        if ($response = parent::__before($request, $user)) {
            return $response;
        }

        $this->active_task_list = DataObjectPool::get('TaskList', $request->getId('task_list_id'));

        if (empty($this->active_task_list)) {
            $this->active_task_list = new TaskList();
            $this->active_task_list->setProject($this->active_project);
        }

        if ($this->active_task_list->getProjectId() != $this->active_project->getId()) {
            return Response::NOT_FOUND;
        }
    }

    /**
     * Show task lists index page.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function index(Request $request, User $user)
    {
        return TaskLists::prepareCollection('active_task_lists_in_project_' . $this->active_project->getId(), $user);
    }

    /**
     * Show task lists index page.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function archive(Request $request, User $user)
    {
        return TaskLists::prepareCollection('archived_task_lists_in_project_' . $this->active_project->getId() . '_page_' . $request->getPage(), $user);
    }

    /**
     * Show all open and closed task lists.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function all_task_lists(Request $request, User $user)
    {
        return TaskLists::prepareCollection('all_task_lists_in_project_' . $this->active_project->getId(), $user);
    }

    /**
     * Reorder task lists.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function reorder(Request $request, User $user)
    {
        if (TaskLists::canReorder($user, $this->active_project)) {
            TaskLists::reorder($request->put(), $this->active_project, $user);

            return $request->put();
        }

        return Response::NOT_FOUND;
    }

    /**
     * Show single task list.
     *
     * @param  Request      $request
     * @param  User         $user
     * @return int|TaskList
     */
    public function view(Request $request, User $user)
    {
        return $this->active_task_list->isLoaded() && $this->active_task_list->canView($user) ? $this->active_task_list : Response::NOT_FOUND;
    }

    /**
     * @param  Request             $request
     * @param  User                $user
     * @return ModelCollection|int
     */
    public function completed_tasks(Request $request, User $user)
    {
        return $this->active_task_list->isLoaded() && $this->active_task_list->canView($user) ? Tasks::prepareCollection('archived_tasks_in_task_list_' . $this->active_task_list->getId() . '_page_' . $request->getPage(), $user) : Response::NOT_FOUND;
    }

    /**
     * Create a new task list.
     *
     * @param  Request      $request
     * @param  User         $user
     * @return TaskList|int
     */
    public function add(Request $request, User $user)
    {
        if (TaskLists::canAdd($user, $this->active_project)) {
            $post = $request->put();

            if (is_array($post)) {
                $post['project_id'] = $this->active_project->getId();
            }

            return TaskLists::create($post);
        }

        return Response::NOT_FOUND;
    }

    /**
     * Edit specific task list.
     *
     * @param  Request      $request
     * @param  User         $user
     * @return TaskList|int
     */
    public function edit(Request $request, User $user)
    {
        return $this->active_task_list->isLoaded() && $this->active_task_list->canEdit($user) ? TaskLists::update($this->active_task_list, $request->put()) : Response::NOT_FOUND;
    }

    /**
     * Edit specific task list.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_task_list->isLoaded() && $this->active_task_list->canDelete($user) ? TaskLists::scrap($this->active_task_list) : Response::NOT_FOUND;
    }

    /**
     * @return TaskList
     */
    public function &getObjectToBeMoved()
    {
        return $this->active_task_list;
    }
}
