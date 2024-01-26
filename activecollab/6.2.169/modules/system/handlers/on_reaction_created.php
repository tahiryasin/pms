<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Handle on_reaction_created event.
 *
 * @param Reaction $reaction
 */
function system_handle_on_reaction_created(Reaction $reaction)
{
    $event_type = 'ReactionCreated';

    AngieApplication::socketsDispatcher()->dispatch($reaction, $event_type);
}
