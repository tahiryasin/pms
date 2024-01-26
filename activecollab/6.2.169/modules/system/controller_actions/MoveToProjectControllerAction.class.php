<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

/**
 * Move to project controller action.
 *
 * @package activeCollab.modules.system
 * @subpackage actions
 */
trait MoveToProjectControllerAction
{
    /**
     * Move object to project.
     *
     * @return DataObject|IProjectElement|int
     */
    public function move_to_project(Request $request, User $user)
    {
        /** @var DataObject|IProjectElement|ITrash $object_to_be_moved */
        $object_to_be_moved = $this->getObjectToBeMoved();

        if ($this->canBeMovedOrCopied($object_to_be_moved)) {
            $target_project = DataObjectPool::get(Project::class, $request->put('project_id'));

            if ($target_project instanceof Project) {
                if ($request->put('copy')) {
                    if ($object_to_be_moved->canCopyToProject($user, $target_project)) {
                        return $object_to_be_moved->copyToProject($target_project, $user);
                    }
                } else {
                    if ($object_to_be_moved->canMoveToProject($user, $target_project)) {
                        $object_to_be_moved->moveToProject($target_project, $user);

                        return $object_to_be_moved;
                    }
                }
            }
        }

        return Response::NOT_FOUND;
    }

    /**
     * Return object that needs to be moved.
     *
     * @return IProjectElement
     */
    abstract public function &getObjectToBeMoved();

    private function canBeMovedOrCopied($object_to_be_moved): bool
    {
        return $object_to_be_moved instanceof IProjectElement
            && $object_to_be_moved instanceof DataObject
            && $object_to_be_moved->isLoaded()
            && !$object_to_be_moved->getIsTrashed();
    }
}
