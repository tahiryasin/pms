<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Authentication\Adapter\AdapterInterface;
use ActiveCollab\Authentication\Adapter\BrowserSessionAdapter;
use ActiveCollab\Authentication\Adapter\TokenBearerAdapter;
use Angie\Authentication\Adapter\AngieTokenHeader;
use Angie\Authentication\Repositories\SessionsRepository;
use Angie\Authentication\Repositories\TokensRepository;
use Angie\Authentication\Repositories\UsersRepository;

/**
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class AuthorizationIntegration extends Integration implements AuthorizationIntegrationInterface
{
    /**
     * @var AdapterInterface[]
     */
    private $adapters = [];

    /**
     * {@inheritdoc}
     */
    public function getAdapters()
    {
        if (empty($this->adapters)) {
            $users_repository = new UsersRepository();
            $sessions_repository = new SessionsRepository();
            $tokens_repository = new TokensRepository();

            $this->adapters = [
                new TokenBearerAdapter($users_repository, $tokens_repository),
                new AngieTokenHeader($users_repository, $tokens_repository),
                new BrowserSessionAdapter(
                    $users_repository,
                    $sessions_repository,
                    AngieApplication::cookies(),
                    AngieApplication::getSessionIdCookieName()
                ),
            ];
        }

        return $this->adapters;
    }

    /**
     * {@inheritdoc}
     */
    public function canInviteUsers()
    {
        return $this->canInviteOwners() || $this->canInviteMembers() || $this->canInviteClients();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'can_invite_owners' => $this->canInviteOwners(),
            'can_invite_members' => $this->canInviteMembers(),
            'can_invite_clients' => $this->canInviteClients(),
            'can_invite_users' => $this->canInviteUsers(),
        ]);
    }
}
