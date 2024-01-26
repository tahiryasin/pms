<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

function system_handle_on_comment_created(Comment $comment)
{
    $event_type = 'CommentCreated';

    Webhooks::dispatch($comment, $event_type);
    AngieApplication::socketsDispatcher()->dispatch($comment, $event_type);
}
