<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Authentication\SecurityLog\EventHandlers;

use ActiveCollab\Authentication\AuthenticatedUser\AuthenticatedUserInterface;

/**
 * @package Angie\Authentication\SecurityLog\EventHandlers
 */
class AuthorizedEventHander extends EventHander
{
    /**
     * @param AuthenticatedUserInterface $user
     */
    public function __invoke(AuthenticatedUserInterface $user)
    {
        $this->getSecurityLog()->recordLogin($user);
    }
}
