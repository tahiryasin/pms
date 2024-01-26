<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Authentication\Authorizer\AuthorizerInterface;
use ActiveCollab\Authentication\Authorizer\LocalAuthorizer;
use Angie\Authentication\PasswordManager\PasswordManager;
use Angie\Authentication\Policies\LoginPolicy;
use Angie\Authentication\Policies\PasswordPolicy;
use Angie\Authentication\Repositories\UsersRepository;

/**
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class LocalAuthorizationIntegration extends AuthorizationIntegration
{
    /**
     * {@inheritdoc}
     */
    public function getAuthorizer()
    {
        return new LocalAuthorizer(new UsersRepository(), AuthorizerInterface::USERNAME_FORMAT_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginPolicy()
    {
        return new LoginPolicy(LoginPolicy::USERNAME_FORMAT_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function getPasswordPolicy()
    {
        return new PasswordPolicy();
    }

    /**
     * {@inheritdoc}
     */
    public function getPasswordManager()
    {
        return AngieApplication::passwordManager();
    }

    /**
     * {@inheritdoc}
     */
    public function canInviteOwners()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canInviteMembers()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canInviteClients()
    {
        return true;
    }
}
