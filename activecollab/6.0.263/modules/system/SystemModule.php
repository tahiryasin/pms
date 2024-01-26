<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Foundation\Events\DataObjectLifeCycleEvent\DataObjectLifeCycleEventInterface;

class SystemModule extends AngieModule
{
    const NAME = 'system';
    const PATH = __DIR__;

    const MAINTENANCE_JOBS_QUEUE_CHANNEL = 'maintenance';

    protected $name = 'system';
    protected $version = '5.0';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            Integration::class,
            function ($ids) {
                return Integrations::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            [
                User::class,
                Member::class,
                Owner::class,
                Client::class,
            ],
            function ($ids) {
                return Users::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            ApiSubscription::class,
            function ($ids) {
                return ApiSubscriptions::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            UserSession::class,
            function ($ids) {
                return UserSessions::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            UserInvitation::class,
            function ($ids) {
                return UserInvitations::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Project::class,
            function ($ids) {
                return Projects::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Company::class,
            function ($ids) {
                return Companies::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Team::class,
            function ($ids) {
                return Teams::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            ProjectTemplate::class,
            function ($ids) {
                return ProjectTemplates::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            [
                ProjectTemplateElement::class,
                ProjectTemplateTaskList::class,
                ProjectTemplateRecurringTask::class,
                ProjectTemplateTask::class,
                ProjectTemplateSubtask::class,
                ProjectTemplateDiscussion::class,
                ProjectTemplateNote::class,
            ],
            function ($ids) {
                return ProjectTemplateElements::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Webhook::class,
            function ($ids) {
                return Webhooks::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Comment::class,
            function ($ids) {
                return Comments::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Reaction::class,
            function ($ids) {
                return Reactions::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            ActivityLog::class,
            function ($ids) {
                return ActivityLogs::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        require_once __DIR__ . '/resources/autoload_model.php';
        require_once __DIR__ . '/models/application_objects/ApplicationObject.class.php';

        AngieApplication::setForAutoload(
            [
                // Errors
                ApiSubscriptionError::class => __DIR__ . '/errors/ApiSubscriptionError.class.php',
                LastOwnerRoleChangeError::class => __DIR__ . '/errors/LastOwnerRoleChangeError.class.php',

                ApplicationObjects::class => __DIR__ . '/models/application_objects/ApplicationObjects.class.php',

                ProjectExportInterface::class => __DIR__ . '/models/ProjectExportInterface.php',
                BaseProjectExport::class => __DIR__ . '/models/BaseProjectExport.php',
                SampleProjectExport::class => __DIR__ . '/models/SampleProjectExport.php',
                ProjectImportInterface::class => __DIR__ . '/models/ProjectImportInterface.php',
                SampleProjectImport::class => __DIR__ . '/models/SampleProjectImport.php',
                ProjectExport::class => __DIR__ . '/models/ProjectExport.php',

                IProjectBasedOn::class => __DIR__ . '/models/IProjectBasedOn.class.php',

                LabelInterface::class => __DIR__ . '/models/LabelInterface.php',
                ProjectLabelInterface::class => __DIR__ . '/models/ProjectLabelInterface.php',
                ProjectLabel::class => __DIR__ . '/models/ProjectLabel.class.php',

                ProjectCategory::class => __DIR__ . '/models/ProjectCategory.class.php',
                ProjectBudgetCollection::class => __DIR__ . '/models/ProjectBudgetCollection.php',
                ProjectAdditionalDataCollection::class => __DIR__ . '/models/ProjectAdditionalDataCollection.php',

                Favorites::class => __DIR__ . '/models/Favorites.class.php',

                MoveToProjectControllerAction::class => __DIR__ . '/controller_actions/MoveToProjectControllerAction.class.php',

                AnonymousUser::class => __DIR__ . '/models/AnonymousUser.class.php',
                Thumbnails::class => __DIR__ . '/models/Thumbnails.class.php',

                IProjectElement::class => __DIR__ . '/models/project_elements/IProjectElement.class.php',
                IProjectElementImplementation::class => __DIR__ . '/models/project_elements/IProjectElementImplementation.class.php',
                IProjectElementsImplementation::class => __DIR__ . '/models/project_elements/IProjectElementsImplementation.class.php',

                // Filters
                AssignmentFilter::class => __DIR__ . '/models/AssignmentFilter.php',
                ProjectsFilter::class => __DIR__ . '/models/ProjectsFilter.php',
                ProjectsTimelineFilter::class => __DIR__ . '/models/ProjectsTimelineFilter.php',
                TeamTimelineFilter::class => __DIR__ . '/models/TeamTimelineFilter.php',

                // Notifications
                NewProjectNotification::class => __DIR__ . '/notifications/NewProjectNotification.class.php',
                NewCommentNotification::class => __DIR__ . '/notifications/NewCommentNotification.class.php',
                NewReactionNotification::class => __DIR__ . '/notifications/NewReactionNotification.class.php',
                PasswordRecoveryNotification::class => __DIR__ . '/notifications/PasswordRecoveryNotification.class.php',
                ReplacingProjectUserNotification::class => __DIR__ . '/notifications/ReplacingProjectUserNotification.class.php',
                NotifyEmailSenderNotification::class => __DIR__ . '/notifications/NotifyEmailSenderNotification.class.php',
                InviteToSharedObjectNotification::class => __DIR__ . '/notifications/InviteToSharedObjectNotification.class.php',
                NewCalendarEventNotification::class => __DIR__ . '/notifications/NewCalendarEventNotification.class.php',
                InfoNotification::class => __DIR__ . '/notifications/InfoNotification.class.php',

                PaymentReceivedNotification::class => __DIR__ . '/notifications/PaymentReceivedNotification.class.php',

                FailedLoginNotification::class => __DIR__ . '/notifications/FailedLoginNotification.class.php',

                BounceEmailNotification::class => __DIR__ . '/notifications/BounceEmailNotification.class.php',
                NotifyOwnersNotification::class => __DIR__ . '/notifications/NotifyOwnersNotification.class.php',

                // Authentication related
                IUser::class => __DIR__ . '/models/IUser.php',

                AuthorizationIntegrationInterface::class => __DIR__ . '/models/integrations/authorization/AuthorizationIntegrationInterface.php',
                AuthorizationIntegration::class => __DIR__ . '/models/integrations/authorization/AuthorizationIntegration.php',
                IdpAuthorizationIntegration::class => __DIR__ . '/models/integrations/authorization/IdpAuthorizationIntegration.php',

                LocalAuthorizationIntegration::class => __DIR__ . '/models/integrations/authorization/LocalAuthorizationIntegration.php',
                ShepherdAuthorizationIntegration::class => __DIR__ . '/models/integrations/authorization/idp/ShepherdAuthorizationIntegration.php',
                OneLoginAuthorizationIntegration::class => __DIR__ . '/models/integrations/authorization/idp/OneLoginAuthorizationIntegration.php',

                // User roles
                Owner::class => __DIR__ . '/models/user_roles/Owner.class.php',
                Member::class => __DIR__ . '/models/user_roles/Member.class.php',
                Client::class => __DIR__ . '/models/user_roles/Client.class.php',

                // Members
                IMembers::class => __DIR__ . '/models/members/IMembers.class.php',
                IBasicMembersImplementation::class => __DIR__ . '/models/members/IBasicMembersImplementation.class.php',
                IMembersImplementation::class => __DIR__ . '/models/members/IMembersImplementation.class.php',
                IMembersViaConnectionTableImplementation::class => __DIR__ . '/models/members/IMembersViaConnectionTableImplementation.class.php',

                // Calendars
                UserCalendar::class => __DIR__ . '/models/UserCalendar.class.php',

                // Morning paper
                MorningPaper::class => __DIR__ . '/models/morning_paper/MorningPaper.php',
                MorningPaperSnapshot::class => __DIR__ . '/models/morning_paper/MorningPaperSnapshot.php',

                // Hourly rates
                IHourlyRates::class => __DIR__ . '/models/hourly_rates/IHourlyRates.class.php',
                IHourlyRatesImplementation::class => __DIR__ . '/models/hourly_rates/IHourlyRatesImplementation.class.php',

                // Project template elements
                ProjectTemplateTaskList::class => __DIR__ . '/models/ProjectTemplateTaskList.php',
                ProjectTemplateTask::class => __DIR__ . '/models/ProjectTemplateTask.php',
                IProjectTemplateTaskDependency::class => __DIR__. '/models/IProjectTemplateTaskDependency.php',
                ProjectTemplateTaskDependenciesCollection::class => __DIR__. '/models/ProjectTemplateTaskDependenciesCollection.php',
                ProjectTemplateRecurringTask::class => __DIR__ . '/models/ProjectTemplateRecurringTask.php',
                ProjectTemplateSubtask::class => __DIR__ . '/models/ProjectTemplateSubtask.php',
                ProjectTemplateDiscussion::class => __DIR__ . '/models/ProjectTemplateDiscussion.php',
                ProjectTemplateNoteGroup::class => __DIR__ . '/models/ProjectTemplateNoteGroup.php',
                ProjectTemplateNote::class => __DIR__ . '/models/ProjectTemplateNote.php',
                ProjectTemplateFile::class => __DIR__ . '/models/ProjectTemplateFile.php',

                InitialSettingsCollection::class => __DIR__ . '/models/initial_settings/InitialSettingsCollection.php',
                InitialUserSettingsCollection::class => __DIR__ . '/models/initial_settings/InitialUserSettingsCollection.php',

                // Reminders
                CustomReminder::class => __DIR__ . '/models/CustomReminder.php',
                CustomReminderNotification::class => __DIR__ . '/notifications/CustomReminderNotification.class.php',

                UserObjectUpdatesCollection::class => __DIR__ . '/models/UserObjectUpdatesCollection.php',

                // Search
                UsersSearchBuilder::class => __DIR__ . '/models/search_builders/UsersSearchBuilder.php',
                CompaniesSearchBuilder::class => __DIR__ . '/models/search_builders/CompaniesSearchBuilder.php',
                ProjectsSearchBuilder::class => __DIR__ . '/models/search_builders/ProjectsSearchBuilder.php',
                ProjectElementsSearchBuilder::class => __DIR__ . '/models/search_builders/ProjectElementsSearchBuilder.php',

                ProjectElementsSearchBuilderInterface::class => __DIR__ . '/models/search_builders/ProjectElementsSearchBuilderInterface.php',

                CompanySearchDocument::class => __DIR__ . '/models/search_documents/CompanySearchDocument.php',
                UserSearchDocument::class => __DIR__ . '/models/search_documents/UserSearchDocument.php',
                ProjectSearchDocument::class => __DIR__ . '/models/search_documents/ProjectSearchDocument.php',
                ProjectElementSearchDocument::class => __DIR__ . '/models/search_documents/ProjectElementSearchDocument.php',

                // Integrations
                IntegrationInterface::class => __DIR__ . '/models/integrations/IntegrationInterface.php',
                DesktopAppIntegration::class => __DIR__ . '/models/integrations/DesktopAppIntegration.php',
                AbstractImporterIntegration::class => __DIR__ . '/models/integrations/AbstractImporterIntegration.class.php',
                BasecampImporterIntegration::class => __DIR__ . '/models/integrations/BasecampImporterIntegration.php',
                ClientPlusIntegration::class => __DIR__ . '/models/integrations/ClientPlusIntegration.php',
                TestLodgeIntegration::class => __DIR__ . '/models/integrations/TestLodgeIntegration.php',
                HubstaffIntegration::class => __DIR__ . '/models/integrations/HubstaffIntegration.php',
                TimeCampIntegration::class => __DIR__ . '/models/integrations/TimeCampIntegration.php',
                ThirdPartyIntegration::class => __DIR__ . '/models/integrations/ThirdPartyIntegration.php',
                TrelloImporterIntegration::class => __DIR__ . '/models/integrations/TrelloImporterIntegration.php',
                AsanaImporterIntegration::class => __DIR__ . '/models/integrations/AsanaImporterIntegration.php',
                CrispIntegration::class => __DIR__ . '/models/integrations/CrispIntegration.php',
                SlackIntegration::class => __DIR__ . '/models/integrations/SlackIntegration.php',
                WarehouseIntegration::class => __DIR__ . '/models/integrations/WarehouseIntegration.php',
                WarehouseIntegrationInterface::class => __DIR__ . '/models/integrations/WarehouseIntegrationInterface.php',
                GoogleDriveIntegration::class => __DIR__ . '/models/integrations/GoogleDriveIntegration.php',
                DropboxIntegration::class => __DIR__ . '/models/integrations/DropboxIntegration.php',
                ZapierIntegration::class => __DIR__ . '/models/integrations/ZapierIntegration.php',
                OneLoginIntegration::class => __DIR__ . '/models/integrations/OneLoginIntegration.php',
                WrikeImporterIntegration::class => __DIR__ . '/models/integrations/WrikeImporterIntegration.php',
                RealTimeIntegration::class => __DIR__ . '/models/integrations/RealTimeIntegration.php',
                RealTimeIntegrationInterface::class => __DIR__ . '/models/integrations/RealTimeIntegrationInterface.php',
                PusherIntegration::class => __DIR__ . '/models/integrations/PusherIntegration.php',
                NewRelicIntegration::class => __DIR__ . '/models/integrations/NewRelicIntegration.php',
                SampleProjectsIntegration::class => __DIR__ . '/models/integrations/SampleProjectsIntegration.php',

                // Attachments Archive
                AttachmentsArchive::class => __DIR__ . '/models/AttachmentsArchive.class.php',

                IHiddenFromClients::class => __DIR__ . '/models/IHiddenFromClients.php',
                INewProjectElementNotificationOptOutConfig::class => __DIR__ . '/models/INewProjectElementNotificationOptOutConfig.php',

                LocalAttachment::class => __DIR__ . '/models/attachments/LocalAttachment.class.php',
                RemoteAttachment::class => __DIR__ . '/models/attachments/RemoteAttachment.class.php',
                WarehouseAttachment::class => __DIR__ . '/models/attachments/WarehouseAttachment.class.php',
                GoogleDriveAttachment::class => __DIR__ . '/models/attachments/GoogleDriveAttachment.class.php',
                DropboxAttachment::class => __DIR__ . '/models/attachments/DropboxAttachment.class.php',

                LocalUploadedFile::class => __DIR__ . '/models/uploaded_files/LocalUploadedFile.class.php',
                RemoteUploadedFile::class => __DIR__ . '/models/uploaded_files/RemoteUploadedFile.class.php',
                WarehouseUploadedFile::class => __DIR__ . '/models/uploaded_files/WarehouseUploadedFile.class.php',
                GoogleDriveUploadedFile::class => __DIR__ . '/models/uploaded_files/GoogleDriveUploadedFile.class.php',
                DropboxUploadedFile::class => __DIR__ . '/models/uploaded_files/DropboxUploadedFile.class.php',

                // Payload transformator
                SlackWebhookPayloadTransformator::class => __DIR__ . '/models/webhooks/SlackWebhookPayloadTransformator.class.php',
                ZapierWebhookPayloadTransformator::class => __DIR__ . '/models/webhooks/ZapierWebhookPayloadTransformator.class.php',
                PusherSocketPayloadTransformator::class => __DIR__ . '/models/webhooks/PusherSocketPayloadTransformator.class.php',

                // Webhooks
                SlackWebhook::class => __DIR__ . '/models/webhooks/SlackWebhook.class.php',

                // Webhooks resolver
                RealTimeUsersChannelsResolver::class => __DIR__ . '/models/webhooks/resolver/RealTimeUsersChannelsResolver.php',
                RealTimeUsersChannelsResolverInterface::class => __DIR__ . '/models/webhooks/resolver/RealTimeUsersChannelsResolverInterface.php',

                Versions::class => __DIR__ . '/models/Versions.php',
                LocalToWarehouseMover::class => __DIR__ . '/models/LocalToWarehouseMover.php',

                UserProfilePermissionsChecker::class => __DIR__ . '/models/UserProfilePermissionsChecker.php',
                OnboardingSurveyInterface::class => __DIR__ . '/models/OnboardingSurvey/OnboardingSurveyInterface.php',
                OnboardingSurvey::class => __DIR__ . '/models/OnboardingSurvey/OnboardingSurvey.php',
                SinceLastVisitServiceInterface::class => __DIR__ . '/models/SinceLastVisitServiceInterface.php',
                SinceLastVisitService::class => __DIR__ . '/models/SinceLastVisitService.php',

                SetupWizard::class => __DIR__ . '/models/SetupWizard/SetupWizard.php',
                SetupWizardInterface::class => __DIR__ . '/models/SetupWizard/SetupWizardInterface.php',

                // CTA Notifications
                FillOnboardingSurveyNotificationInterface::class => __DIR__ . '/models/CTANotification/FillOnboardingSurveyNotificationInterface.php',
                CTANotificationInterface::class => __DIR__ . '/models/CTANotification/CTANotificationInterface.php',
                FillOnboardingSurveyNotification::class => __DIR__ . '/models/CTANotification/FillOnboardingSurveyNotification.php',
                FillOnboardingSurveyNotificationStageResolver::class => __DIR__ . '/models/CTANotification/FillOnboardingSurveyNotificationStageResolver.php',
                CTANotifications::class => __DIR__ . '/models/CTANotification/CTANotifications.php',

                // Crisp Notifications
                CrispUserNotificationsResolver::class => __DIR__ . '/models/CrispNotifications/CrispUserNotificationsResolver.php',
                CrispNotificationForNewUser::class => __DIR__ . '/models/CrispNotifications/CrispNotificationForNewUser.php',
                CrispNotificationForExistingUser::class => __DIR__ . '/models/CrispNotifications/CrispNotificationForExistingUser.php',
                CrispNotificationInterface::class => __DIR__ . '/models/CrispNotifications/CrispNotificationInterface.php',

                // Comments.
                IComments::class => __DIR__ . '/models/comments/IComments.php',
                ICommentsImplementation::class => __DIR__ . '/models/comments/ICommentsImplementation.php',

                CommentCreatedActivityLog::class => __DIR__ . '/models/CommentCreatedActivityLog.php',

                // Reactions.
                IReactions::class => __DIR__ . '/models/reactions/IReactions.php',
                IReactionsImplementation::class => __DIR__ . '/models/reactions/IReactionsImplementation.php',
                SmileReaction::class => __DIR__ . '/models/reactions/SmileReaction.php',
                ThinkingReaction::class => __DIR__ . '/models/reactions/ThinkingReaction.php',
                ThumbsUpReaction::class => __DIR__ . '/models/reactions/ThumbsUpReaction.php',
                ThumbsDownReaction::class => __DIR__ . '/models/reactions/ThumbsDownReaction.php',
                ApplauseReaction::class => __DIR__ . '/models/reactions/ApplauseReaction.php',
                HeartReaction::class => __DIR__ . '/models/reactions/HeartReaction.php',
                PartyReaction::class => __DIR__ . '/models/reactions/PartyReaction.php',

                // Activity logs.
                IActivityLog::class => __DIR__ . '/models/activity_logs/IActivityLog.php',
                IActivityLogImplementation::class => __DIR__ . '/models/activity_logs/IActivityLogImplementation.php',

                InstanceCreatedActivityLog::class => __DIR__ . '/models/InstanceCreatedActivityLog.php',
                InstanceUpdatedActivityLog::class => __DIR__ . '/models/InstanceUpdatedActivityLog.php',

                IActivityLogsCollection::class => __DIR__ . '/models/activity_log_collections/IActivityLogsCollection.php',
                ActivityLogsInCollection::class => __DIR__ . '/models/activity_log_collections/ActivityLogsInCollection.php',

                UserActivityLogsCollection::class => __DIR__ . '/models/activity_log_collections/UserActivityLogsCollection.php',
                UserActivityLogsForCollection::class => __DIR__ . '/models/activity_log_collections/UserActivityLogsForCollection.php',
                UserActivityLogsByCollection::class => __DIR__ . '/models/activity_log_collections/UserActivityLogsByCollection.php',

                DailyUserActivityLogsForCollection::class => __DIR__ . '/models/activity_log_collections/DailyUserActivityLogsForCollection.php',

                // Notifications.
                StorageOverusedNotification::class => __DIR__ . '/notifications/StorageOverusedNotification.class.php',
            ]
        );
    }

    public function defineHandlers()
    {
        $this->listen('on_notification_inspector');

        $this->listen('on_rebuild_activity_logs');

        $this->listen('on_extra_stats');

        $this->listen('on_handle_public_subscribe');
        $this->listen('on_handle_public_unsubscribe');

        $this->listen('on_available_integrations');
        $this->listen('on_initial_settings');
        $this->listen('on_available_webhook_payload_transformators');

        $this->listen('on_visible_object_paths');
        $this->listen('on_trash_sections');
        $this->listen('on_search_filters');
        $this->listen('on_search_rebuild_index');
        $this->listen('on_user_access_search_filter');
        $this->listen('on_report_sections');
        $this->listen('on_reports');

        $this->listen('on_morning_mail');

        $this->listen('on_protected_config_options');
        $this->listen('on_history_field_renderers');

        $this->listen('on_company_created');
        $this->listen('on_moved_to_trash');
        $this->listen('on_restored_from_trash');

        $this->listen('on_daily_maintenance');
        $this->listen('on_reset_manager_states');
        $this->listen('on_user_invitation_accepted');
        $this->listen('on_session_started');

        $this->listen('on_resets_initial_settings_timestamp');

        $this->listen('on_smile_reaction_created', 'on_reaction_created');
        $this->listen('on_heart_reaction_created', 'on_reaction_created');
        $this->listen('on_thumbs_up_reaction_created', 'on_reaction_created');
        $this->listen('on_thumbs_down_reaction_created', 'on_reaction_created');
        $this->listen('on_thinking_reaction_created', 'on_reaction_created');
        $this->listen('on_applause_reaction_created', 'on_reaction_created');
        $this->listen('on_party_reaction_created', 'on_reaction_created');

        $this->listen('on_smile_reaction_deleted', 'on_reaction_deleted');
        $this->listen('on_heart_reaction_deleted', 'on_reaction_deleted');
        $this->listen('on_thumbs_up_reaction_deleted', 'on_reaction_deleted');
        $this->listen('on_thumbs_down_reaction_deleted', 'on_reaction_deleted');
        $this->listen('on_thinking_reaction_deleted', 'on_reaction_deleted');
        $this->listen('on_applause_reaction_deleted', 'on_reaction_deleted');
        $this->listen('on_party_reaction_deleted', 'on_reaction_deleted');

        $this->listen('on_comment_created', 'on_comment_created');
        $this->listen('on_email_received', 'on_email_received');
    }

    public function defineListeners(): array
    {
        $listeners = [
            // Listen for all data object life cycle events, capture ones that need to send a webhook, and prepare jobs
            // that will matching webhooks with the object payload.
            DataObjectLifeCycleEventInterface::class => AngieApplication::webhookDispatcher(),
        ];

        return $listeners;
    }
}
