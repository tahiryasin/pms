<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Utils\NewFeatures\NewFeatureAnnouncement;
use ActiveCollab\Module\System\Utils\NewFeatures\NewFeatureAnnouncementInterface;

const FEATURE_VISIBILITY_MANAGER = 'manager';

return [
    new NewFeatureAnnouncement(
        lang('Task Dependencies in Templates'),
        lang('Automate your workflow even further with task dependencies in project templates.'),
        new DateValue('2019-06-05'),
        lang('Go to Templates'),
        'project-templates'
    ),
    new NewFeatureAnnouncement(
        lang('Time Estimates in Templates'),
        lang('Specify how much time each task should take in project templates, and have that value set when projects are created from templates.'),
        new DateValue('2019-05-20'),
        lang('Go to Templates'),
        'project-templates'
    ),
    new NewFeatureAnnouncement(
        lang('Timeline Export'),
        lang('Export the timeline into PDF or PNG so you can share, print, use it in presentations, etc.'),
        new DateValue('2019-05-10'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Improved Move/Copy Permissions'),
        lang('More people can move items between projects, which reduces friction for teams that use multi-project workflows.'),
        new DateValue('2019-05-10'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Additional Recurring Task Intervals'),
        lang("You'll have more control over task scheduling and planning with additional recurring task intervals."),
        new DateValue('2019-04-22'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Neon Theme'),
        lang('Give your eyes a bit of relief by choosing the Neon Theme.'),
        new DateValue('2019-02-27'),
        lang('Change Theme'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Duplicate Project Templates'),
        lang('Duplicate existing project templates without having to start all over if you just need to make minor adjustments.'),
        new DateValue('2019-02-26'),
        lang('Go to Templates'),
        'project-templates'
    ),
    new NewFeatureAnnouncement(
        lang('Automatic Rescheduling'),
        lang('When one task needs to be rescheduled, you can also automatically reschedule all dependent activities.'),
        new DateValue('2019-01-10'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Task Dependencies'),
        lang('Set dependencies between tasks to clearly communicate execution order.'),
        new DateValue('2019-01-10'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Themes & Customization'),
        lang('Customization options evolved to the next level, with several new themes to choose from and complement your work style preferences.'),
        new DateValue('2019-01-10'),
        lang('Change Theme'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Improved Attachment Navigation'),
        lang('Navigate through your attachments by using the left and right arrow keys, or by clicking the left and right icons with your mouse.'),
        new DateValue('2018-10-05'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Live Comments'),
        lang('See when your colleagues and clients are typing, and have their comments appear instantly when they are sent.'),
        new DateValue('2018-09-24'),
        lang('Go to Projects'),
        'projects',
        null,
        [
            NewFeatureAnnouncementInterface::CHANNEL_CLOUD,
        ]
    ),
    new NewFeatureAnnouncement(
        lang('Task Input Protection'),
        lang('System warns users if they are closing non-empty task forms, making accidental input loss less likely to happen.'),
        new DateValue('2018-09-24'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('More Languages'),
        lang('ActiveCollab is now available in 20 languages! Latest additions are Japanese, Romanian and Slovak.'),
        new DateValue('2018-09-21'),
        lang('Change Language'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Default Job Type'),
        lang('ActiveCollab remembers which job type people are using to track time, and selects it automatically.'),
        new DateValue('2018-08-03'),
        lang('Go to Projects'),
        'projects',
        function (User $user) {
            return $user->isMember();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Lithuanian Language'),
        lang('Use ActiveCollab in Lithuanian.'),
        new DateValue('2018-07-29'),
        lang('Change Language'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Comment Reactions'),
        lang("See something you like? Punch the like button. Don't like what you see, give it a thumb down."),
        new DateValue('2018-06-12'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Since Last Visit'),
        lang('Never miss comments that have been posted since your last visit to a task or a discussion.'),
        new DateValue('2018-05-30'),
        lang('Learn More'),
        'https://activecollab.com/blog/product/since-your-last-visit'
    ),
    new NewFeatureAnnouncement(
        lang('Croatian Language'),
        lang('Use ActiveCollab in Croatian.'),
        new DateValue('2018-03-31'),
        lang('Change Language'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('ActiveCollab Subscription Page'),
        lang('Easily access your ActiveCollab subscription details in one place, including all you invoices.'),
        new DateValue('2018-03-19'),
        lang('Go to Subscription'),
        'subscription',
        function (User $user) {
            return $user->isOwner();
        },
        [
            NewFeatureAnnouncementInterface::CHANNEL_CLOUD,
        ]
    ),
    new NewFeatureAnnouncement(
        lang('Desktop App'),
        lang('Run ActiveCollab as an app on your Mac or Windows computer.'),
        new DateValue('2018-02-23'),
        lang('Download the App'),
        'integrations/desktop-app',
        function (User $user) {
            return $user->isMember(false);
        }
    ),
    new NewFeatureAnnouncement(
        lang('Sample Projects'),
        lang('There are many ways to use ActiveCollab. Check out Sample Projects to get inspired.'),
        new DateValue('2018-01-29'),
        lang('Create a Sample Project'),
        'integrations/sample-projects',
        function (User $user) {
            return $user->isMember(false);
        }
    ),
    new NewFeatureAnnouncement(
        lang('Better Search'),
        lang('Search is faster, and offers more filtering and sorting options.'),
        new DateValue('2017-12-26'),
        lang('Go to Search'),
        'search'
    ),
    new NewFeatureAnnouncement(
        lang('Hungarian Language'),
        lang('Use ActiveCollab in Hungarian.'),
        new DateValue('2017-12-16'),
        lang('Change Language'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Performance Improvements'),
        lang('Navigate ActiveCollab faster than ever before thanks to frontend, backend and infrastructure optimizations.'),
        new DateValue('2017-11-07'),
        lang('Go to My Work'),
        'my-work',
        function (User $user) {
            return $user->isMember(false);
        }
    ),
    new NewFeatureAnnouncement(
        lang('Copy Images from Clipboard'),
        lang('Copy images and paste them directly into a text field when you are adding tasks, writing comments, etc.'),
        new DateValue('2017-10-04'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Zapier Integration'),
        lang('Connect ActiveCollab to more than 750 different apps using Zapier.'),
        new DateValue('2017-03-13'),
        lang('Go to the Add-On'),
        'integrations/zapier',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Column Sort'),
        lang('Sort column values in a report by clicking on the column name.'),
        new DateValue('2017-03-13'),
        lang('Go to Reports'),
        'reports',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isManager());
        }
    ),
    new NewFeatureAnnouncement(
        lang('Even More Languages'),
        lang('Use ActiveCollab in Czech, Italian, Portuguese, Serbian, or Turkish.'),
        new DateValue('2017-01-17'),
        lang('Change Language'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('More Languages'),
        lang('Use ActiveCollab in Russian or Swedish (in addition to Dutch, English, French, German, Polish, and Spanish).'),
        new DateValue('2016-10-04'),
        lang('Change Language'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Email Notification Settings'),
        lang('Control how much email you want to receive from ActiveCollab.'),
        new DateValue('2016-09-07'),
        lang('Go to Preferences'),
        'settings'
    ),
    new NewFeatureAnnouncement(
        lang('Google Drive Integration'),
        lang('Share your Google Drive documents as attachments in ActiveCollab.'),
        new DateValue('2016-09-07'),
        lang('Go to the Add-On'),
        'integrations/google-drive',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Dropbox Integration'),
        lang('Share Dropbox files on projects with your team.'),
        new DateValue('2016-09-07'),
        lang('Go to the Add-On'),
        'integrations/dropbox',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Advanced Mode for Time Report'),
        lang('Advanced time search and filtering.'),
        new DateValue('2016-08-16'),
        lang('Go to the Report'),
        'reports/time-tracking',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isPowerUser());
        }
    ),
    new NewFeatureAnnouncement(
        lang('Projects Report'),
        lang('List all the projects that meet certain criteria.'),
        new DateValue('2016-08-16'),
        lang('Go to the Report'),
        'reports/projects',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isPowerUser());
        }
    ),
    new NewFeatureAnnouncement(
        lang('Invoices Report'),
        lang('Find all the invoices you need that meet certain criteria.'),
        new DateValue('2016-08-16'),
        lang('Go to the Report'),
        'reports/invoices',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isFinancialManager());
        }
    ),
    new NewFeatureAnnouncement(
        lang('Xero Integration'),
        lang('Create invoices from billable time and expenses in ActiveCollab, and then send them to your Xero account for further processing.'),
        new DateValue('2016-08-16'),
        lang('Go to the Add-On'),
        'integrations/xero',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('iCalendar Projects Feed'),
        lang('Import project task and task list dates into your favorite calendar app so you can see what is coming up.'),
        new DateValue('2016-08-07'),
        lang('Learn More'),
        'https://help.activecollab.com/books/activity/calendar.html#s-exporting-calendar-feeds',
        function (User $user) {
            return $user->isMember(true);
        }
    ),
    new NewFeatureAnnouncement(
        lang('Hubstaff Integration'),
        lang('Track time on ActiveCollab tasks using the Hubstaff timer and keep time logs synced across both systems.'),
        new DateValue('2016-07-04'),
        lang('Go to the Add-On'),
        'integrations/hubstaff',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Team Timeline'),
        lang('See how much work each team member has on a Gantt-like timeline so you can plan and manage their workload better.'),
        new DateValue('2016-07-04'),
        lang('Go to the Report'),
        'reports/team-timeline',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isPowerUser());
        }
    ),
    new NewFeatureAnnouncement(
        lang('Projects Timeline'),
        lang('See all your projects on a Gantt-like timeline so you can plan and manage them better.'),
        new DateValue('2016-07-04'),
        lang('Go to the Report'),
        'reports/projects-timeline',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isPowerUser());
        }
    ),
    new NewFeatureAnnouncement(
        lang('More Languages'),
        lang('Use ActiveCollab in Dutch, Italian, and Polish (in addition to English, French, German, and Spanish).'),
        new DateValue('2016-07-04'),
        lang('Change Language on the Profile Page'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Recurring Tasks'),
        lang('Save time by letting ActiveCollab automatically create recurring tasks every day, week, or month.'),
        new DateValue('2016-05-19'),
        lang('Learn More'),
        'https://blog.activecollab.com/product/2016/05/19/recurring-tasks.html',
        function (User $user) {
            return $user->isMember(true);
        }
    ),
    new NewFeatureAnnouncement(
        lang('Exact Time and Date on Comments'),
        lang('Hover the mouse over when a comment was posted, and the exact time and date will appear in a few seconds.'),
        new DateValue('2016-05-19'),
        lang('Go to Projects'),
        'projects'
    ),
    new NewFeatureAnnouncement(
        lang('Quick Jump'),
        lang('Instantly open any part of ActiveCollab or project by pressing CMD+K (Mac) or CTRL+K (Windows).'),
        new DateValue('2016-05-19'),
        lang('Learn Other Shortcuts'),
        'https://help.activecollab.com/books/my-active-collab/keyboard-shortcuts.html'
    ),
    new NewFeatureAnnouncement(
        lang('Client+'),
        lang('Give clients a Client+ role so they can create and assign tasks as well as be an assignee.'),
        new DateValue('2016-03-30'),
        lang('Go to the Add-On'),
        'integrations/client-plus',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Slack Integration'),
        lang('Connect ActiveCollab projects to a Slack channel to receive notifications whenever something happens on your projects.'),
        new DateValue('2016-03-30'),
        lang('Go to the Integration'),
        'integrations/slack',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Custom Date Ranges in Time Report'),
        lang('Pick a custom date range in the Time, Expense, or Payments report.'),
        new DateValue('2016-03-30'),
        lang('Go to the Reports'),
        'reports',
        function (User $user) {
            return $user->isOwner() || ($user->isMember() && $user->isPowerUser());
        }
    ),
    new NewFeatureAnnouncement(
        lang('QuickBooks Integration'),
        lang('Create invoices from billable time and expenses in ActiveCollab, and then send them to your QuickBooks account for further processing.'),
        new DateValue('2016-02-03'),
        lang('Go to the Integration'),
        'integrations/quickbooks',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Languages'),
        lang('Use ActiveCollab in English, French, German, or Spanish.'),
        new DateValue('2016-02-03'),
        lang('Change Language on the Profile Page'),
        'profile'
    ),
    new NewFeatureAnnouncement(
        lang('Trello Importer'),
        lang('Copy your Trello projects into ActiveCollab.'),
        new DateValue('2016-02-03'),
        lang('Go to the Integration'),
        'integrations/trello-importer',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Basecamp Importer'),
        lang('Copy your Basecamp projects into ActiveCollab.'),
        new DateValue('2015-11-13'),
        lang('Go to the Integration'),
        'integrations/basecamp-importer',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Webhooks'),
        lang('Notify 3rd party services about what’s happening in ActiveCollab. Webhooks lets you integrate with existing apps for custom workflows.'),
        new DateValue('2015-11-13'),
        lang('Go to the Integration'),
        'integrations/webhooks',
        function (User $user) {
            return $user->isOwner();
        }
    ),
    new NewFeatureAnnouncement(
        lang('Mail to Project'),
        lang('Create tasks and post comments directly from your email. When you get an email notification, just send a reply to post a comment. Send emails to a project email address to create tasks.'),
        new DateValue('2015-11-13'),
        lang('See How It Works'),
        'https://help.activecollab.com/books/projects/email-to-projects.html'
    ),
    new NewFeatureAnnouncement(
        lang('Timeline View'),
        lang('Manage and schedule all your tasks in a Gantt-like timeline.'),
        new DateValue('2015-11-13'),
        lang('See How It Works'),
        'https://help.activecollab.com/books/projects/tasks.html#s-using-task-views'
    ),
    new NewFeatureAnnouncement(
        lang('Column View'),
        lang('See your tasks as cards on a Kanban board and move them across columns.'),
        new DateValue('2015-11-13'),
        lang('See How It Works'),
        'https://help.activecollab.com/books/projects/tasks.html#s-using-task-views'
    ),
];
