<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * SystemNotification class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class SystemNotification extends FwSystemNotification
{
    public function isHandledInternally()
    {
        return false;
    }
}
