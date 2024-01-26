<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_company_created event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */

/**
 * Handle on_company_created event.
 *
 * @param Company $company
 * @param array   $attributes
 */
function system_handle_on_company_created(Company $company, array $attributes)
{
    Webhooks::dispatch($company, 'CompanyCreated');
}
