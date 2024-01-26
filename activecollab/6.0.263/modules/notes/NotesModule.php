<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

class NotesModule extends AngieModule
{
    const NAME = 'notes';

    protected $name = 'notes';
    protected $version = '5.0';

    public function init()
    {
        parent::init();

        DataObjectPool::registerTypeLoader(
            NoteGroup::class,
            function ($ids) {
                return NoteGroups::findByIds($ids);
            }
        );

        DataObjectPool::registerTypeLoader(
            Note::class,
            function ($ids) {
                return Notes::findByIds($ids);
            }
        );
    }

    public function defineClasses()
    {
        require_once __DIR__ . '/resources/autoload_model.php';

        AngieApplication::setForAutoload(
            [
                NewNoteNotification::class => __DIR__ . '/notifications/NewNoteNotification.class.php',
            ]
        );
    }

    public function defineHandlers()
    {
        $this->listen('on_rebuild_activity_logs');
        $this->listen('on_trash_sections');
        $this->listen('on_reset_manager_states');
        $this->listen('on_note_created');
    }
}
