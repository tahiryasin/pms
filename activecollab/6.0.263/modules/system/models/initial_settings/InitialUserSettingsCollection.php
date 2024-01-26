<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Initial user settings collection.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class InitialUserSettingsCollection extends FwInitialUserSettingsCollection
{
    /**
     * {@inheritdoc}
     */
    protected function onLoadSettings(array &$settings, User $user)
    {
        $options = [
            'display_mode_projects',
            'display_mode_project_files',
            'display_mode_project_tasks',
            'display_mode_project_time',
            'display_mode_invoices',
            'display_mode_estimates',
            'group_mode_people',
            'sort_mode_projects',
            'sort_mode_project_notes',
            'default_project_label_id',
            'my_work_projects_order',
            'my_work_collapsed_sections',
            'show_visual_editor_toolbar',
            'filter_client_projects',
            'filter_label_projects',
            'filter_category_projects',
            'updates_show_notifications',
            'updates_play_sound',
            'search_sort_preference',
        ];

        $values = ConfigOptions::getValuesFor($options, $user);

        foreach ($options as $option) {
            $settings[$option] = $values[$option];
        }

        if (empty($settings['my_work_projects_order'])) {
            $settings['my_work_projects_order'] = [];
        }

        if (empty($settings['my_work_collapsed_sections'])) {
            $settings['my_work_collapsed_sections'] = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function onLoadCollections(array &$collections, User $user)
    {
        $collections['users'] = Users::prepareCollection(DataManager::ALL, $user);
        $collections['companies'] = Companies::prepareCollection(DataManager::ALL, $user);
        $collections['projects'] = Projects::prepareCollection('active_projects_page_1', $user);
        $collections['system_notifications'] = SystemNotifications::prepareCollection('active_recipient_system_notifications', $user);
    }
}
