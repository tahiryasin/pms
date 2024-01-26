<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

class DiscussionsModule extends AngieModule
{
    const NAME = 'discussions';

    protected $name = 'discussions';
    protected $version = '5.0';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            Discussion::class,
            function ($ids) {
                return Discussions::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        require_once __DIR__ . '/resources/autoload_model.php';

        AngieApplication::setForAutoload(
            [
                ProjectDiscussionsCollection::class => __DIR__ . '/models/ProjectDiscussionsCollection.php',
                NewDiscussionNotification::class => __DIR__ . '/notifications/NewDiscussionNotification.class.php',
            ]
        );
    }

    public function defineHandlers()
    {
        $this->listen('on_rebuild_activity_logs');
        $this->listen('on_object_from_notification_context');
        $this->listen('on_trash_sections');
        $this->listen('on_reset_manager_states');
        $this->listen('on_discussion_created');
    }
}
