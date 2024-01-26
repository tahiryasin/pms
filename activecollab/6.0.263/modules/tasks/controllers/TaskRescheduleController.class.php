<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\OnDemand\Utils\TaskDateRescheduler\TaskDateRescheduler;
use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\StatusResponse\StatusResponse;

AngieApplication::useController('auth_required', SystemModule::NAME);

class TaskRescheduleController extends AuthRequiredController
{
    /**
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

        $this->active_task = DataObjectPool::get(Task::class, $request->getId('task_id'));

        if (empty($this->active_task)) {
            return Response::NOT_FOUND;
        }

        if (!$this->active_task->canView($user) || !$this->active_task->canEdit($user)) {
            return new StatusResponse(
                Response::FORBIDDEN,
                '',
                ['message' => lang('Access not allowed.')]
            );
        }

        return null;
    }

    public function reschedule_simulation(Request $request, $user)
    {
        $params = $request->getQueryParams();

        if (!isset($params['due_on'])) {
            return Response::BAD_REQUEST;
        }

        $due_on = DateValue::makeFromString($params['due_on']);
        $start_on = null;

        if (isset($params['start_on'])) {
            $start_on = DateValue::makeFromString($params['start_on']);
        }

        if (!($start_on instanceof DateValue)) {
            if ($this->active_task->getStartOn() instanceof DateValue) {
                $start_on = clone $this->active_task->getStartOn();
            } else {
                $start_on = clone $due_on;
            }
        }

        AngieApplication::skippableTaskDatesCorrector()->correctDates($this->active_task, $start_on, $due_on);

        return AngieApplication::taskDateRescheduler()->simulateReschedule(
            $this->active_task,
            $due_on
        );
    }

    public function make_reschedule(Request $request, $user)
    {
        $post = $request->post();

        if (!isset($post['due_on']) || !isset($post['start_on'])) {
            return Response::BAD_REQUEST;
        }

        $start_on = DateValue::makeFromString((string) $post['start_on']);
        $due_on = DateValue::makeFromString((string) $post['due_on']);

        $simulation = !empty($post['simulation']) && is_array($post['simulation']) ? $post['simulation'] : null;

        $task_rescheduler = AngieApplication::taskDateRescheduler();

        try {
            AngieApplication::skippableTaskDatesCorrector()->correctDates($this->active_task, $start_on, $due_on);

            $new_simulation = $task_rescheduler->simulateReschedule($this->active_task, $due_on);

            if ($simulation && !$task_rescheduler->isSimulationIdentical($simulation, $new_simulation)) {
                return new StatusResponse(
                    Response::CONFLICT,
                    '',
                    $new_simulation
                );
            }

            return $task_rescheduler->updateSimulationTaskDates(
                $task_rescheduler->updateInitialTaskDate($this->active_task, $start_on, $due_on),
                $new_simulation,
                $user
            );
        } catch (Exception $e) {
            AngieApplication::log()->error(
                'Error while rescheduling task dates. Reason: {reason}',
                [
                    'reason' => $e->getMessage(),
                    'exception' => $e,
                ]
            );

            return new StatusResponse(
                Response::BAD_REQUEST,
                '',
                ['message' => lang('Something went wrong. Please try again or contact customer support.')]
            );
        }
    }
}
