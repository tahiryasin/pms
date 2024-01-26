<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.system
 * @subpackage resources
 */
AngieApplication::useModel(
    [
        'access_logs',
        'activity_logs',
        'api_subscriptions',
        'attachments',
        'availability_records',
        'availability_types',
        'calendar_events',
        'calendars',
        'categories',
        'comments',
        'companies',
        'currencies',
        'data_filters',
        'day_offs',
        'integrations',
        'labels',
        'languages',
        'modification_logs',
        'notifications',
        'payment_gateways',
        'payments',
        'project_template_elements',
        'project_template_task_dependencies',
        'project_templates',
        'projects',
        'reactions',
        'reminders',
        'stored_cards',
        'subscriptions',
        'system_notifications',
        'teams',
        'test_data_objects',
        'uploaded_files',
        'user_invitations',
        'user_sessions',
        'user_workspaces',
        'users',
        'webhooks',
        'feature_pointers',
        'feature_pointer_dismissals',
    ],
    'system'
);
