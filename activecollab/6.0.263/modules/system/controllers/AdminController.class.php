<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('auth_required', SystemModule::NAME);

/**
 * Base administration controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class AdminController extends AuthRequiredController
{
    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if (!$user->isOwner()) {
            return Response::NOT_FOUND;
        }
    }
}
