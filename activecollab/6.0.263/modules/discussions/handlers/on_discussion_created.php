<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_discussion_created event handler.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage handlers
 */

/**
 * Handle on_discussion_created event.
 *
 * @param Discussion $discussion
 */
function discussions_handle_on_discussion_created(Discussion $discussion)
{
    Webhooks::dispatch($discussion, 'DiscussionCreated');
}
