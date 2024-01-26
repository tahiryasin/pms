<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\Tracking\Services\StopwatchServiceInterface;
use ActiveCollab\Module\Tracking\Utils\StopwatchManagerInterface;
use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\StatusResponse\StatusResponse;

AngieApplication::useController('auth_required', SystemModule::NAME);

class StopwatchController extends AuthRequiredController
{
    public function __before(Request $request, $user)
    {
        if ($response = parent::__before($request, $user)) {
            return $response;
        }

        if ($user->isClient()) {
            return new StatusResponse(
                Response::FORBIDDEN,
                '',
                ['message' => lang('Only owners and members can access.')]
            );
        }

        return null;
    }

    public function index(Request $request, User $user)
    {
        return AngieApplication::getContainer()
            ->get(StopwatchManagerInterface::class)
            ->getStopwatchesForUser($user);
    }

    public function start(Request $request, User $user)
    {
        $post = $request->post();

        $parent_type = (string) $post['parent_type'];
        $parent_id = (int) $post['parent_id'];

        if (!$this->denyAccessUnlessIsGranted($user, $parent_type, $parent_id)) {
            return Response::FORBIDDEN;
        }

        try {
            return AngieApplication::getContainer()
                ->get(StopwatchServiceInterface::class)
                ->start(
                    $user,
                    $parent_type,
                    $parent_id
                );
        } catch (InvalidArgumentException $exception) {
            $this->logErrorException($exception);

            return new StatusResponse(
                Response::BAD_REQUEST,
                '',
                ['message' => lang('Invalid arguments provided for stopwatch')]
            );
        } catch (Exception $exception) {
            $this->logErrorException($exception);

            return new StatusResponse(
                Response::OPERATION_FAILED,
                '',
                ['message' => lang('Something went wrong. Please try again or contact customer support.')]
            );
        }
    }

    public function resume(Request $request, User $user)
    {
        $id = $request->get('id');
        $stopwatch = AngieApplication::getContainer()
            ->get(StopwatchManagerInterface::class)
            ->findOneByUserAndId($user->getId(), (int) $id);

        if (!$stopwatch) {
            return new StatusResponse(
                Response::NOT_FOUND,
                '',
                ['message' => lang('Stopwatch does not exist.')]
            );
        }

        return AngieApplication::getContainer()
            ->get(StopwatchServiceInterface::class)
            ->resume($stopwatch);
    }

    public function pause(Request $request, User $user)
    {
        $id = $request->get('id');
        $stopwatch = AngieApplication::getContainer()
            ->get(StopwatchManagerInterface::class)
            ->findOneByUserAndId($user->getId(), (int) $id);

        if (!$stopwatch) {
            return new StatusResponse(
                Response::NOT_FOUND,
                '',
                ['message' => lang('Stopwatch does not exist.')]
            );
        }

        return AngieApplication::getContainer()
            ->get(StopwatchServiceInterface::class)
            ->pause($stopwatch);
    }

    private function denyAccessUnlessIsGranted(User $user, string $parent_type, int $parent_id)
    {
        if ($parent_type === Project::class) {
            /** @var Project $project */
            $project = Projects::findOneBy([
                'id' => $parent_id,
                'is_trashed' => false,
            ]);

            return $project && $project->canView($user);
        }

        if ($parent_type === Task::class) {
            /** @var Task $task */
            $task = Tasks::findOneBy([
                'id' => $parent_id,
                'is_trashed' => false,
            ]);

            return $task && $task->canView($user);
        }

        return false;
    }

    private function logErrorException(Exception $exception)
    {
        AngieApplication::log()->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
