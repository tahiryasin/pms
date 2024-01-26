<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class RealTimeUsersChannelsResolver implements RealTimeUsersChannelsResolverInterface
{
    /**
     * get user channel for object.
     *
     * @param  DataObject $object
     * @return array
     */
    public function getUsersChannels(DataObject $object)
    {
        $channels = [];
        if ($object instanceof IWhoCanSeeThis) {
            foreach ($object->whoCanSeeThis() as $user_id) {
                if (AngieApplication::isOnDemand()) {
                    $account_id = AngieApplication::getAccountId();
                    $channels[] = "private-instance-{$account_id}-user-{$user_id}";
                } else {
                    $channels[] = "private-user-{$user_id}";
                }
            }
        }

        return $channels;
    }
}
