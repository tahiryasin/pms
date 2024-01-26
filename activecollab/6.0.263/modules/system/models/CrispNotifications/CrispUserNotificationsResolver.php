<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class CrispUserNotificationsResolver
{
    /**
     * @var User
     */
    private $user;

    /**
     * CrispNotificationsResolver constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        if ($user instanceof Client) {
            throw new LogicException('Crisp notifications are only available for members');
        }

        $this->user = $user;
    }

    /**
     * Resolve a notification by slug or by class name.
     *
     * For slug you would pass something like 'for-existing-user'
     * For class name you would pass FQCN like 'CrispNotificationForExistingUser'
     *
     * Example calls:
     *
     * AngieApplication::CrispNotification()->resolveNotification($user, 'for-existing-user');
     * AngieApplication::CrispNotification()->resolveNotification($user, CrispNotificationForExistingUser::class);
     *
     * @param                             $slug_or_class_name
     * @return CrispNotificationInterface
     * @throws InvalidArgumentException
     */
    public function resolveNotification($slug_or_class_name)
    {
        switch ($slug_or_class_name) {
            case CrispNotificationForExistingUser::SLUG:
            case CrispNotificationForExistingUser::class:
                return new CrispNotificationForExistingUser(
                    $this->user
                );
            case CrispNotificationForNewUser::SLUG:
            case CrispNotificationForNewUser::class:
                return new CrispNotificationForNewUser(
                    $this->user
                );
            default:
                throw new InvalidArgumentException('Unknown Crisp notification type');
        }
    }
}
