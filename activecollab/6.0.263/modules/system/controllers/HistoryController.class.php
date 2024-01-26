<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('fw_history', HistoryFramework::NAME);

/**
 * Application level history controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class HistoryController extends FwHistoryController
{
}
