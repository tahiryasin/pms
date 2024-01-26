<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * on_user_invitation_accepted event handler.
 *
 * @package ActiveCollab.modules.system
 * @subpackage handlers
 */
use ActiveCollab\Module\OnDemand\Utils\SubscribeToNewsletterService\SubscribeToNewsletterServiceInterface;

/**
 * User accepted invitation event handler.
 */
function system_handle_on_user_invitation_accepted(User $user)
{
    Webhooks::dispatch($user, 'UserAcceptedInvitation');

    if (AngieApplication::isOnDemand()) {
        AngieApplication::acceptPolicyService()->accept($user, AngieApplication::getCurrentPolicyVersion());
        AngieApplication::getContainer()->get(SubscribeToNewsletterServiceInterface::class)->subscribeInvitedUser($user);
    }
}
