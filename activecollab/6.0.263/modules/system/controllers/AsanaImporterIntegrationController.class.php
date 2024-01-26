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
 * Asana integration controller.
 */
class AsanaImporterIntegrationController extends IntegrationSingletonsController
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

        if (!$this->active_integration instanceof AsanaImporterIntegration) {
            return Response::CONFLICT;
        }
    }

    /**
     * @param  Request $request
     * @return mixed
     */
    public function authorize(Request $request)
    {
        $this->active_integration = Integrations::findFirstByType(AsanaImporterIntegration::class)->authorize($request->put());

        return $this->active_integration->validateCredentials();
    }

    /**
     * @param  Request   $request
     * @return int
     * @throws Exception
     */
    public function schedule_import(Request $request)
    {
        $selected_workspaces = $request->post('selected_workspaces');

        if (empty($selected_workspaces)) {
            throw new Exception('Must select at least one workspace to import.');
        } else {
            $this->active_integration->setSelectedWorkspaces($selected_workspaces);

            return $this->active_integration->scheduleImport();
        }
    }

    /**
     * @return mixed
     */
    public function start_over()
    {
        return $this->active_integration->startOver();
    }

    /**
     * @return mixed
     */
    public function check_status()
    {
        return $this->active_integration->checkStatus();
    }

    /**
     * @return mixed
     */
    public function invite_users()
    {
        return $this->active_integration->invite();
    }
}
