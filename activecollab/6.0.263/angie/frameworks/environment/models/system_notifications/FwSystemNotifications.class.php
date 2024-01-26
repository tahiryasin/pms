<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level system notification manager implementation.
 *
 * @package angie.environment
 * @subpackage models
 */
abstract class FwSystemNotifications extends BaseSystemNotifications
{
    /**
     * Check should this notification needs to be displayed.
     *
     * @return bool
     */
    public static function shouldBeRaised()
    {
        return false;
    }

    /**
     * Clear all notifications of this type.
     */
    public static function clearNotifications()
    {
        parent::delete(['type = ?', static::getType()]);
    }

    /**
     * Return 'type' attribute for polymorh model creation.
     *
     * @return string
     * @throws NotImplementedError
     */
    public static function getType()
    {
        throw new NotImplementedError(__METHOD__);
    }

    /**
     * Return new collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if (str_starts_with($collection_name, 'active_recipient_system_notifications')) {
            $collection->setConditions('recipient_id = ? AND is_dismissed = ?', $user->getId(), 0);
        } elseif (str_starts_with($collection_name, 'all_recipient_system_notifications')) {
            $collection->setConditions('recipient_id = ?', $user->getId());
        }

        return $collection;
    }

    /**
     * Add this notification.
     *
     * @param $user
     * @return bool
     */
    public static function add(Owner $user = null)
    {
        if ($user instanceof Owner) {
            $to_users = [$user];
        } else {
            $to_users = Users::findOwners();
        }

        foreach ($to_users as $user) {
            $system_notification = self::findByRecipientId($user->getId());

            if (!$system_notification) { //id doesn't exists
                $attributes = [
                    'recipient_id' => $user->getId(),
                ];
                self::create($attributes);
            } else {
                if ($system_notification instanceof SystemNotification && !$system_notification->isPermanent() && $system_notification->getIsDismissed()) { //if is dismissed and it should be shown again
                    $system_notification->setIsDismissed(false);
                    $system_notification->save();
                }
            }
        }

        return true;
    }

    /**
     * Find by recipient id.
     *
     * @param $recipient_id
     * @return SystemNotification[]|DBResult|null
     */
    public static function findByRecipientId($recipient_id)
    {
        return self::find([
            'conditions' => ['type = ? AND recipient_id = ?', static::getType(), $recipient_id],
            'one' => true,
        ]);
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $attributes['type'] = static::getType();
        $attributes['created_on'] = new DateTimeValue();

        parent::create($attributes, $save, $announce);
    }
}
