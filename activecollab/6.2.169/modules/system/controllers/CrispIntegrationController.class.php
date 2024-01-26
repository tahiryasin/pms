<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('integration_singletons', SystemModule::NAME);

/**
 * Crisp integration controller.
 */
class CrispIntegrationController extends IntegrationSingletonsController
{
    /**
     * @var CrispIntegration|Integration
     */
    protected $active_integration;

    public function enable_crisp(Request $request, User $user)
    {
        $this->active_integration->enableCrisp($user);

        return $this->active_integration->getDataForUser($user);
    }

    public function disable_crisp(Request $request, User $user)
    {
        $this->active_integration->disableCrisp($user);

        return $this->active_integration->getDataForUser($user);
    }

    public function notifications(Request $request, User $user)
    {
        return $this->active_integration->getNotifications($user);
    }

    public function enable_notification(Request $request, User $user)
    {
        $notification_slug = trim($request->get('type'));

        try {
            return $this->active_integration->enableNotification($user, $notification_slug);
        } catch (InvalidArgumentException $e) {
            return Response::NOT_FOUND;
        } catch (LogicException | Exception $e) {
            AngieApplication::log()->warning("Unable to dismiss notification  {$notification_slug}: " . $e->getMessage());

            return Response::BAD_REQUEST;
        }
    }

    public function dismiss_notification(Request $request, User $user)
    {
        $notification_slug = trim($request->get('type'));

        try {
            return $this->active_integration->dismissNotification($user, $notification_slug);
        } catch (InvalidArgumentException $e) {
            return Response::NOT_FOUND;
        } catch (LogicException | Exception $e) {
            AngieApplication::log()->warning("Unable to dismiss notification  {$notification_slug}: " . $e->getMessage());

            return Response::BAD_REQUEST;
        }
    }

    public function info_for_user(Request $request, User $user)
    {
        return $this->active_integration->getDataForUser($user);
    }
}
