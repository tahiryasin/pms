<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('auth_not_required', SystemModule::NAME);

/**
 * Application level password recovery controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class PasswordRecoveryController extends AuthNotRequiredController
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

        if (!AngieApplication::authentication()->getLoginPolicy()->isPasswordRecoveryEnabled()) {
            return Response::NOT_FOUND;
        }

        return null;
    }

    /**
     * Send reset password code.
     *
     * @param  Request   $request
     * @return array|int
     */
    public function send_code(Request $request)
    {
        $username = $request->post('username');

        $user = is_valid_email($username) ? Users::findByEmail($username, true) : null;

        if ($user instanceof User && $user->isActive()) {
            return $user->beginPasswordRecovery();
        }

        return Response::BAD_REQUEST;
    }

    /**
     * Verify code and reset user password.
     *
     * @param  Request $request
     * @return User
     */
    public function reset_password(Request $request)
    {
        return Users::finishPasswordRecovery($request->post('user_id'), $request->post('code'), $request->post('password'));
    }
}
