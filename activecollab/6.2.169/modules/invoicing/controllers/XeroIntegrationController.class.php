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
 * Xero integration controller.
 */
class XeroIntegrationController extends AuthRequiredController
{
    /**
     * {@inheritdoc}
     */
    public function __before(Request $request, $user)
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
     * Return xero data like clients, tax_rate etc...
     *
     * @return array
     */
    public function get_data()
    {
        return [
            'clients' => Integrations::findFirstByType('XeroIntegration')->getCompanies(),
            'accounts' => Integrations::findFirstByType('XeroIntegration')->getAccounts(),
        ];
    }

    /**
     * Get request url.
     *
     * @return bool
     */
    public function get_request_url()
    {
        return ['request_url' => Integrations::findFirstByType('XeroIntegration')->getRequestUrl()];
    }

    /**
     * Authorize.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        return Integrations::findFirstByType('XeroIntegration')->authorize($request->put());
    }
}
