<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('auth_required', EnvironmentFramework::INJECT_INTO);

/**
 * System Notifications controller.
 *
 * @package angie.frameworks.environment
 * @subpackage controllers
 */
class FwSystemNotificationsController extends AuthRequiredController
{
    /**
     * @var SystemNotification
     */
    protected $active_notification;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        $this->active_notification = DataObjectPool::get('SystemNotification', $request->getId('notification_id'));
    }

    /**
     * Return all system notifications.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function index(Request $request, User $user)
    {
        return SystemNotifications::prepareCollection($user);
    }

    /**
     * Dismiss.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return bool|int
     */
    public function dismiss(Request $request, User $user)
    {
        return $this->active_notification->isLoaded() && $this->active_notification->canDismiss($user) ? $this->active_notification->dismiss() : Response::NOT_FOUND;
    }
}
