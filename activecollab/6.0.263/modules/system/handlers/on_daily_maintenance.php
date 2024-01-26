<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_daily_maintenance event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_daily_maintenance event.
 */
function system_handle_on_daily_maintenance()
{
    Notifications::cleanUp();

    ApiSubscriptions::deleteExpired();
    UserSessions::deleteExpired();
    UserInvitations::cleanUp();

    AngieApplication::securityLog()->cleanUp();

    (new LocalToWarehouseMover())->moveFilesToWarehouse();
}
