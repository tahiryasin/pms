<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Module\Tracking\Utils;

use ActiveCollab\Foundation\App\AccountId\AccountIdResolverInterface;
use Angie\Notifications\NotificationsInterface;
use BudgetThresholds;
use BudgetThresholdsNotifications;
use DateTimeValue;
use DB;
use Exception;
use Project;
use Psr\Log\LoggerInterface;
use Users;

class BudgetNotificationsMaintenanceRunner implements BudgetNotificationsMaintenanceRunnerInterface
{
    private $notifications_service;
    private $budget_notifications_manager;
    private $account_id_resolver;
    private $logger;

    public function __construct(
        NotificationsInterface $notifications_service,
        BudgetNotificationsManagerInterface $budget_notifications_manager,
        AccountIdResolverInterface $account_id_resolver,
        LoggerInterface $logger
    )
    {
        $this->notifications_service = $notifications_service;
        $this->budget_notifications_manager = $budget_notifications_manager;
        $this->account_id_resolver = $account_id_resolver;
        $this->logger = $logger;
    }

    public function run() {
        $this->logger->info(
            sprintf(
                'Running project budget notification maintenance for account #%d.',
                $this->account_id_resolver->getAccountId()
            )
        );
        $projects = $this->budget_notifications_manager->findProjectsThatReachedThreshold();
        foreach ($projects as $project) {
            $this->runProjectsBudgetNotifying($project['project'], $project['threshold']);
        }

        $this->logger->info(
            sprintf(
                'Finished project budget notification maintenance for account #%d',
                $this->account_id_resolver->getAccountId()
            )
        );
    }

    public function runProjectsBudgetNotifying($project, $threshold)
    {
        try {
            DB::beginWork('Running project budget maintenance @ '. __CLASS__);
            $recipient = $this->getRecipientsForMail($project);
            $this->sendNotificationForBudgetThresholdReached($project, $recipient, $threshold['threshold']);
            DB::commit('Finished project budget notifying @ ' . __CLASS__);
        } catch (Exception $exception){
            DB::rollback('Rollback project budget notifying action @ ' . __CLASS__);
            $this->logger->error('Failed to maintain project budget notifications.', [
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    public function sendNotificationForBudgetThresholdReached(Project $project, array $users, int $threshold)
    {
        $this->notifications_service
            ->notifyAbout('tracking/budget_threshold_reached', $project)
            ->setProjectName($project->getName())
            ->setProjectUrl($project->getViewUrl())
            ->setThreshold($threshold)
            ->sendToUsers($users, true);

        $budgetThreshold = BudgetThresholds::findOneBy(['project_id' => $project->getId(), 'threshold' => $threshold]);

        foreach ($users as $user) {
            BudgetThresholdsNotifications::create([
                'parent_id' => $budgetThreshold->getId(),
                'user_id' => $user->getId(),
                'sent_at' => DateTimeValue::now()->toMySQL(),
            ]);
        }
    }

    public function getRecipientsForMail(Project $project): array
    {
        if ($project->getLeader() && $project->getLeader()->isActive()) {
            return [$project->getLeader()];
        } elseif ($project->getCreatedById() && $project->getCreatedBy()->isActive()) {
            return [$project->getCreatedBy()];
        }

        $ids = $project->getMemberIds();
        $recipients = [];

        foreach ($ids as $id) {
            $user = Users::findById($id);
            if ($user->isFinancialManager() && $user->isActive()) {
                array_push($recipients, $user);
            }
        }

        return $recipients;
    }
}
