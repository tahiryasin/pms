<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_morning_mail event handler.
 *
 * @package angie.framework.reminders
 * @subpackage handlers
 */

/**
 * Do frequently check.
 */
function reminders_handle_on_morning_mail()
{
    Reminders::send();
}
