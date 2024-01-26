<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('fw_upgrade', EnvironmentFramework::NAME);

/**
 * Application level upgrade controller.
 *
 * @package ActiveCollab.modules.system
 * @subpackage controllers
 */
class UpgradeController extends FwUpgradeController
{
}
