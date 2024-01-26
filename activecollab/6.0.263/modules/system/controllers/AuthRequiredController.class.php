<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('fw_auth_required', EnvironmentFramework::NAME);

/**
 * Authentication required controller.
 *
 * @package angie.frameworks.environment
 * @subpackage controllers
 */
class AuthRequiredController extends FwAuthRequiredController
{
}
