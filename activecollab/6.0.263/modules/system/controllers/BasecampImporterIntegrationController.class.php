<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

AngieApplication::useController('integration_singletons', SystemModule::NAME);

use Angie\Http\Request;
use Angie\Http\Response;

/**
 * Cron integrations controller.
 *
 * @property BasecampImporterIntegration $active_integration
 * @package activecollab.modules.system
 * @subpackage controllers
 */
class BasecampImporterIntegrationController extends IntegrationSingletonsController
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

        if (!($this->active_integration instanceof BasecampImporterIntegration)) {
            return Response::CONFLICT;
        }
    }

    /**
     * Check credentials.
     *
     * @param  Request $request
     * @return array
     */
    public function check_credentials(Request $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $application_id = $request->post('account_id');

        if (!$username || !$password || !$application_id) {
            return Response::BAD_REQUEST;
        }

        $this->active_integration->setCredentials($username, $password, $application_id);

        return $this->active_integration->validateCredentials();
    }

    /**
     * Start import.
     *
     * @param  Request                     $request
     * @return BasecampImporterIntegration
     */
    public function schedule_import(Request $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $application_id = $request->post('account_id');

        if (!$username || !$password || !$application_id) {
            return Response::BAD_REQUEST;
        }

        $this->active_integration->setCredentials($username, $password, $application_id);

        return $this->active_integration->scheduleImport();
    }

    /**
     * Start the process over.
     *
     * @return BasecampImporterIntegration
     */
    public function start_over()
    {
        return $this->active_integration->startOver();
    }

    /**
     * Check progress of the importer.
     *
     * @return BasecampImporterIntegration
     */
    public function check_status()
    {
        return $this->active_integration->checkStatus();
    }

    /**
     * Send users invite.
     *
     * @return BasecampImporterIntegration
     */
    public function invite_users()
    {
        return $this->active_integration->invite();
    }
}
