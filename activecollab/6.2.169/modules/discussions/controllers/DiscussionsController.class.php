<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\MovedResource\MovedResource;

AngieApplication::useController('project', SystemModule::NAME);

/**
 * Discussions controller.
 *
 * @package activeCollab.modules.discussions
 * @subpackage controllers
 */
class DiscussionsController extends ProjectController
{
    use MoveToProjectControllerAction;

    /**
     * Selected discussion.
     *
     * @var Discussion
     */
    protected $active_discussion;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        if ($response = parent::__before($request, $user)) {
            return $response;
        }

        if ($discussion_id = $request->getId('discussion_id')) {
            $this->active_discussion = DataObjectPool::get('Discussion', $request->getId('discussion_id'));

            if (empty($this->active_discussion)) {
                /** @var Task $converted_to_task */
                if ($converted_to_task = Tasks::findByDiscussionId($discussion_id)) {
                    return new MovedResource($converted_to_task->getViewUrl(), true);
                }

                return Response::NOT_FOUND; // Discussion not found
            }
        }

        if ($this->active_discussion instanceof Discussion) {
            if ($this->active_discussion->getProjectId() != $this->active_project->getId()) {
                return Response::NOT_FOUND;
            }
        } else {
            $this->active_discussion = new Discussion();
            $this->active_discussion->setProject($this->active_project);
        }
    }

    /**
     * Show discussions module homepage.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function index(Request $request, User $user)
    {
        AccessLogs::logAccess($this->active_project, $user);

        return Discussions::prepareCollection('discussions_in_project_' . $this->active_project->getId() . '_page_' . $request->getPage(), $user);
    }

    /**
     * Get read status for project discussions.
     *
     * @param  Request $request
     * @param  User    $user
     * @return array
     */
    public function read_status(Request $request, User $user)
    {
        return Discussions::getReadStatusInProject($user, $this->active_project);
    }

    /**
     * View specific discussion.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return Discussion|int
     */
    public function view(Request $request, User $user)
    {
        return $this->active_discussion->isLoaded() && $this->active_discussion->canView($user) ? AccessLogs::logAccess($this->active_discussion, $user) : Response::NOT_FOUND;
    }

    /**
     * Create a new discussion.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return Discussion|int
     */
    public function add(Request $request, User $user)
    {
        if (Discussions::canAdd($user, $this->active_project)) {
            $post = $request->post();

            if ($post && is_array($post)) {
                $post['project_id'] = $this->active_project->getId();
            }

            return Discussions::create($post);
        }

        return Response::NOT_FOUND;
    }

    /**
     * Update discussion.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return Discussion|int
     */
    public function edit(Request $request, User $user)
    {
        return $this->active_discussion->isLoaded() && $this->active_discussion->canEdit($user) ? Discussions::update($this->active_discussion, $request->put()) : Response::NOT_FOUND;
    }

    /**
     * Drop a selected discussion.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return Discussion|int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_discussion->isLoaded() && $this->active_discussion->canDelete($user) ? Discussions::scrap($this->active_discussion) : Response::NOT_FOUND;
    }

    /**
     * @param  Request  $request
     * @param  User     $user
     * @return Task|int
     */
    public function promote_to_task(Request $request, User $user)
    {
        if ($this->active_discussion->isLoaded() && $this->active_discussion->canEdit($user)) {
            return Discussions::promoteToTask($this->active_discussion, $user);
        }

        return Response::FORBIDDEN;
    }

    /**
     * @return Discussion
     */
    public function &getObjectToBeMoved()
    {
        return $this->active_discussion;
    }
}
