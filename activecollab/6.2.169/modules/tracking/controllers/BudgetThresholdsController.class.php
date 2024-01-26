<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\StatusResponse\StatusResponse;

AngieApplication::useController('auth_required', SystemModule::NAME);

class BudgetThresholdsController extends AuthRequiredController
{
    /**
     * Active project.
     *
     * @var Project
     */
    protected $active_project;

    protected function __before(Request $request, $user)
    {
        if ($before_result = parent::__before($request, $user)) {
            return $before_result;
        }

        if ($project_id = $request->getId('project_id')) {
            if ($this->active_project = DataObjectPool::get('Project', $project_id)) {
                if (!$this->active_project->canSeeBudget($user)) {
                    return Response::FORBIDDEN;
                }
            } else {
                return Response::NOT_FOUND;
            }
        } else {
            $this->active_project = new Project();
        }

        return null;
    }

    public function index(Request $request, User $user)
    {
        try {
            return BudgetThresholds::prepareCollection('budget_thresholds_for_' . $request->get('project_id'), $user);
        } catch (Exception $exception) {
            return new StatusResponse(
                Response::OPERATION_FAILED,
                '',
                ['message' => lang('Something went wrong. Thresholds defined for this Project cannot be shown.')]
            );
        }
    }

    public function add(Request $request, User $user)
    {
        $post = $request->post();
        $projectId = $post['project_id'];

        /** @var Project $project */
        $project = Projects::findOneBy([
            'id' => $projectId,
        ]);

        if ($project && !$project->canSeeBudget($user)) {
            return Response::FORBIDDEN;
        }

        $existing_project_thresholds = BudgetThresholds::findBy(
            [
                'project_id' => $projectId,
            ]
        );

        $existing_thresholds_values = [];

        if (!$existing_project_thresholds) {
            foreach ($post['attributes'] as $received_threshold) {
                $threshold = [
                    'project_id' => $projectId,
                    'type' => 'income',
                    'threshold' => $received_threshold,
                    'created_on' => new DateTime(),
                    'created_by_id' => $user->getId(),
                    'created_by_name' => $user->getFullName(),
                    'created_by_email' => $user->getEmail(),
                ];
                $existing_project_thresholds[] = BudgetThresholds::create($threshold);
            }

            return $existing_project_thresholds;
        }

        foreach ($existing_project_thresholds->toArray() as $key => $old_threshold) {
            $existing_thresholds_values[] = $old_threshold->getThreshold();

            if (!in_array($old_threshold->getThreshold(), $post['attributes'])) {
                if (($key = array_search($old_threshold->getThreshold(), $existing_thresholds_values)) !== false) {
                    unset($existing_thresholds_values[$key]);
                }
                BudgetThresholds::scrap($old_threshold, true);
            }
        }

        if (!empty($post['attributes'])) {
            foreach ($post['attributes'] as $received_threshold_value) {
                foreach ($existing_project_thresholds->toArray() as $old_threshold) {
                    if (!in_array($received_threshold_value, $existing_thresholds_values)) {
                        $threshold = [
                            'project_id' => $projectId,
                            'type' => 'income',
                            'threshold' => $received_threshold_value,
                            'created_on' => new DateTime(),
                            'created_by_id' => $user->getId(),
                            'created_by_name' => $user->getFullName(),
                            'created_by_email' => $user->getEmail(),
                        ];
                        BudgetThresholds::create($threshold);
                        $existing_thresholds_values[] = $received_threshold_value;
                    }
                }
            }
        }
    }
}
