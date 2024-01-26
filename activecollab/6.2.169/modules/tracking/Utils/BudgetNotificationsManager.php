<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

namespace ActiveCollab\Module\Tracking\Utils;

use DateTimeValue;
use DB;
use DBQueryError;
use InvalidParamError;
use Project;
use Projects;
use Users;

class BudgetNotificationsManager implements BudgetNotificationsManagerInterface
{
    /**
     * Returns all projects that were updated in the last hour.
     * @throws DBQueryError
     * @throws InvalidParamError
     */
    public function getProjectsIds(): array
    {
        $query = 'SELECT DISTINCT p.id as project_id FROM projects p
            JOIN budget_thresholds bt
            ON p.id = bt.project_id
            WHERE p.updated_on > ?
            AND p.is_tracking_enabled > 0
            AND p.budget > 0
            AND p.is_trashed = 0
            AND p.is_sample = 0';

        $result = DB::executeFirstColumn($query, new DateTimeValue('- 2 HOURS'));

        $projects = [];

        if ($result) {
            $projects = $result;
        }

        return $projects;
    }

    public function findProjectsThatReachedThreshold(): array
    {
        $projects_to_notify = [];
        $updated_project_ids = $this->getProjectsIds();
        if (!empty($updated_project_ids)) {
            foreach ($updated_project_ids as $project_id) {
                /** @var Project $project */
                $project = Projects::findById($project_id);
                $spent_in_percents = $project->getCostSoFarInPercent(Users::findFirstOwner());
                $thresholds = $this->getProjectThresholds($project_id);

                $highest_threshold = null;
                foreach ($thresholds as $threshold) {
                    if (intval($threshold['threshold']) <= intval($spent_in_percents)) {
                        $highest_threshold = $threshold;
                    }
                }

                if ($highest_threshold && !$this->isNotificationSentFor($highest_threshold['id'])) {
                    $projects_to_notify[] = [
                        'project' => $project,
                        'threshold' => [
                            'threshold' => $highest_threshold['threshold'],
                            'threshold_id' => $highest_threshold['id'],
                        ],
                    ];
                }
            }
        }

        return $projects_to_notify;
    }

    public function getProjectThresholds(int $id): array
    {
        $query = '
            SELECT bt.id, bt.threshold FROM budget_thresholds bt
            WHERE bt.project_id = ?
            ORDER BY bt.threshold ASC';

        $result = DB::execute($query, $id);

        if ($result) {
            return $result->toArray();
        }

        return [];
    }

    public function isNotificationSentFor(int $threshold_id): bool
    {
        return (bool) DB::executeFirstCell('SELECT id FROM budget_thresholds_notifications WHERE parent_id = ? AND DATE(sent_at) = CURRENT_DATE()', $threshold_id);
    }
}
