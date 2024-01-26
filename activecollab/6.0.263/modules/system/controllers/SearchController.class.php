<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Search\SearchResult\SearchResultInterface;

AngieApplication::useController('auth_required', EnvironmentFramework::INJECT_INTO);

/**
 * Application level search controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class SearchController extends AuthRequiredController
{
    /**
     * Query the index.
     *
     * @param  Request               $request
     * @param  User                  $user
     * @return SearchResultInterface
     */
    public function query(Request $request, User $user)
    {
        return AngieApplication::search()->query(
            $request->get('q'),
            $user,
            AngieApplication::search()->getCriterionsFromRequest($request->get()),
            $request->getPage(),
            100
        );
    }
}
