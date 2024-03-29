<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('fw_currencies', EnvironmentFramework::NAME);

/**
 * Application level currencies controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class CurrenciesController extends FwCurrenciesController
{
}
