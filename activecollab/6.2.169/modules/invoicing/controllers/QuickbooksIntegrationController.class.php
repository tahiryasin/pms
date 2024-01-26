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
 * Quickbooks integration controller.
 */
class QuickbooksIntegrationController extends AuthRequiredController
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

        if (!$user->isFinancialManager()) {
            return Response::NOT_FOUND;
        }
    }

    /**
     * Return quickbooks data like clients, tax_rate etc...
     *
     * @param  Request $request
     * @return array
     */
    public function get_data(Request $request)
    {
        return Integrations::findFirstByType('QuickbooksIntegration')->fetch($request->get('entity'), $request->get('ids', []), false);
    }

    /**
     * Get request url.
     *
     * @return bool
     */
    public function get_request_url()
    {
        return ['request_url' => Integrations::findFirstByType('QuickbooksIntegration')->getRequestUrl()];
    }

    /**
     * Authorize.
     *
     * @param  Request $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return Integrations::findFirstByType('QuickbooksIntegration')->authorize($request->put());
    }
}
