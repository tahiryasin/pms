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
 * Wrike integration controller.
 */
class WrikeImporterIntegrationController extends IntegrationSingletonsController
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

        if (!($this->active_integration instanceof WrikeImporterIntegration)) {
            return Response::CONFLICT;
        }
    }

    /**
     * Authorize Wrike.
     *
     * @param  Request $request
     * @return mixed
     */
    public function authorize(Request $request)
    {
        $authorize_code = array_var($request->put(), 'authorize_code');

        if (empty($authorize_code) || !is_string($authorize_code)) {
            return Response::BAD_REQUEST;
        }

        $this->active_integration = Integrations::findFirstByType('WrikeImporterIntegration')->authorize($authorize_code);

        return $this->active_integration->validateCredentials();
    }

    /**
     * Start Import.
     *
     * @param  Request   $request
     * @return array|int
     */
    public function schedule_import(Request $request)
    {
        $account_id = $request->post('account_id');

        if (!isset($account_id)) {
            return Response::BAD_REQUEST;
        }

        $this->active_integration->setAccountId($account_id);

        return $this->active_integration->scheduleImport();
    }

    /**
     * Check progress of the importer.
     *
     * @return mixed
     */
    public function check_status()
    {
        return $this->active_integration->checkStatus();
    }

    /**
     * Start the process over.
     *
     * @return TrelloImporterIntegration
     */
    public function start_over()
    {
        return $this->active_integration->startOver();
    }

    public function invite_users()
    {
        return $this->active_integration->invite();
    }
}
