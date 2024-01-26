<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * New discussion notification.
 *
 * @package ActiveCollab.modules.discussions
 * @subpackage notifications
 */
class NewDiscussionNotification extends Notification
{
    use INewInstanceUpdate;
    use INewProjectElementNotificationOptOutConfig;
}
