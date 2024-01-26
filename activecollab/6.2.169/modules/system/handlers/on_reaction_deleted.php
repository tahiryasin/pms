<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Handle on_reaction_deleted event.
 *
 * @param Reaction $reaction
 */
function system_handle_on_reaction_deleted(Reaction $reaction)
{
    $event_type = 'ReactionDeleted';

    AngieApplication::socketsDispatcher()->dispatch($reaction, $event_type);
}
