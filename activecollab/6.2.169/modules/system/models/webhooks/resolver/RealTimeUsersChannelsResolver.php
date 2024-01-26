<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

class RealTimeUsersChannelsResolver implements RealTimeUsersChannelsResolverInterface
{
    public function getUsersChannels(DataObject $object, bool $for_partial_object = false): array
    {
        $user_ids = [];

        if ($object instanceof IWhoCanSeeThis) {
            $user_ids = $object->whoCanSeeThis();

            // all power users should get real-time events for time records/expenses
            if ($object instanceof ITrackingObject) {
                $power_user_ids = Users::findIdsByType(
                    Member::class,
                    $user_ids,
                    function ($id, $type, $custom_permissions) {
                        return in_array(User::CAN_MANAGE_PROJECTS, $custom_permissions);
                    }
                );

                if (is_array($power_user_ids)) {
                    $user_ids = array_unique(array_merge($user_ids, $power_user_ids));
                }
            }
        } elseif ($object instanceof IMembers) {
            $user_ids = array_unique(
                array_merge(
                    Users::findOwnerIds(),
                    $object->getMemberIds()
                )
            );
        } elseif ($object instanceof IUser) {
            $user_ids = Users::getIdsWhoCanSeeUser($object);
        }

        if ($for_partial_object) {
            // send partial data to all member plus users who cannot see the object
            $user_ids = Users::findIdsByType(
                Member::class,
                $user_ids,
                function ($id, $type, $custom_permissions) {
                    return in_array(User::CAN_MANAGE_PROJECTS, $custom_permissions);
                }
            );

            $user_ids = is_array($user_ids) ? $user_ids : [];
        }

        return $this->makeChannels($user_ids);
    }

    private function makeChannels(array $user_ids): array
    {
        $channels = [];

        foreach ($user_ids as $user_id) {
            if (AngieApplication::isOnDemand()) {
                $channels[] = sprintf(
                    'private-instance-%s-user-%s',
                    AngieApplication::getAccountId(),
                    $user_id
                );
            } else {
                $channels[] = sprintf(
                    'private-user-%s',
                    $user_id
                );
            }
        }

        return $channels;
    }
}
