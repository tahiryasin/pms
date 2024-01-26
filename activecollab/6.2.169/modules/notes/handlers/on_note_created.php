<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_note_created event handler.
 *
 * @package ActiveCollab.modules.notes
 * @subpackage handlers
 */

/**
 * Handle on_note_created event.
 *
 * @param Note  $note
 * @param array $attributes
 */
function notes_handle_on_note_created(Note $note, array $attributes)
{
    Webhooks::dispatch($note, 'NoteCreated');
}
