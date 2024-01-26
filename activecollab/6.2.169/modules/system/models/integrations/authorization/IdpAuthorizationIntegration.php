<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Authentication\Authorizer\SamlAuthorizer;
use Angie\Authentication\ExceptionHandler\SamlExceptionHandler;
use Angie\Authentication\Repositories\UsersRepository;
use Angie\Authentication\RequestProcessor\ShepherdRequestProcessor;

abstract class IdpAuthorizationIntegration extends AuthorizationIntegration
{
    public function getAuthorizer()
    {
        return new SamlAuthorizer(
            new UsersRepository(),
            new ShepherdRequestProcessor(AngieApplication::currentTimestamp()),
            new SamlExceptionHandler()
        );
    }

    public function getConsumerServiceUrl(): string
    {
        return ROOT_URL . '/api/v1/user-session';
    }

    public function getIssuer(): string
    {
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        return str_starts_with($url, ROOT_URL, false) ? $url : ROOT_URL;
    }
}
