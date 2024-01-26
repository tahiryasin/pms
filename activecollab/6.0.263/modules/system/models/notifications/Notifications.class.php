<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Notifications manager class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Notifications extends BaseNotifications
{
    const READ_CACHE_KEY = 'notifications_read';

    /**
     * {@inheritdoc}
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'notifications_for_recipient') || str_starts_with($collection_name, 'unread_notifications_for_recipient')) {
            return self::prepareNotificationsForRecipientCollection($collection_name, $user);
        } else {
            throw new InvalidParamError('collection_name', $collection_name);
        }
    }

    /**
     * @param  string                      $collection_name
     * @param  User|null                   $user
     * @return UserNotificationsCollection
     * @throws ImpossibleCollectionError
     */
    private static function prepareNotificationsForRecipientCollection($collection_name, $user)
    {
        $bits = explode('_', $collection_name);
        $recipient = DataObjectPool::get(User::class, array_pop($bits));

        if ($recipient instanceof User && $user->isActive()) {
            $collection = parent::prepareCollection($collection_name, $user);

            $collection->setOrderBy('created_on DESC, id DESC');
            $collection->setJoinTable('notification_recipients', 'notification_id');

            if (str_starts_with($collection_name, 'notifications_for_recipient')) {
                $collection->setConditions('notification_recipients.recipient_id = ?', $recipient->getId());
            } else {
                $collection->setConditions('notification_recipients.recipient_id = ? AND notification_recipients.read_on IS NULL', $recipient->getId());
            }

            return $collection;
        }

        throw new ImpossibleCollectionError('Recipient not found or found, but not active');
    }

    // ---------------------------------------------------
    //  Read/Unread
    // ---------------------------------------------------

    /**
     * Returns true if $user has read context in which notification was published.
     *
     * @param  Notification|int     $notification
     * @param  User                 $user
     * @param  bool                 $use_cache
     * @param  bool                 $rebuild_stale_cache
     * @return bool
     * @throws InvalidInstanceError
     */
    public static function isRead($notification, User $user, $use_cache = true, $rebuild_stale_cache = true)
    {
        if ($user instanceof User) {
            return self::isReadTimestampSet($notification, $user, $use_cache, $rebuild_stale_cache);
        }

        throw new InvalidInstanceError('user', $user, 'User');
    }

    /**
     * Mark a single notification as read.
     *
     * @param  Notification $notification
     * @param  User         $user
     * @throws Exception
     */
    public static function markRead(Notification $notification, User $user)
    {
        if (!self::isRead($notification, $user, false, false)) {
            DB::execute('UPDATE notification_recipients SET read_on = UTC_TIMESTAMP() WHERE notification_id = ? AND recipient_id = ?', $notification->getId(), $user->getId());

            AngieApplication::cache()->removeByObject($notification);

            // Update read cache only if cache value exists (if not, system will rebuild it the first time it is needed)
            $cached_value = AngieApplication::cache()->getByObject($user, self::READ_CACHE_KEY);

            if (is_array($cached_value)) {
                $cached_value[$notification->getId()] = true;

                AngieApplication::cache()->setByObject($user, self::READ_CACHE_KEY, $cached_value);
            }
        }
    }

    /**
     * Mark all unread notifications for a given object as read.
     *
     * @param  ApplicationObject|array $parent
     * @param  User                    $user
     * @throws Exception
     * @throws InvalidParamError
     */
    public static function markReadByParent($parent, User $user)
    {
        if (is_array($parent) && isset($object[0]) && $object[1]) {
            [$parent_type, $parent_id] = $parent;
        } elseif ($parent instanceof ApplicationObject) {
            $parent_type = get_class($parent);
            $parent_id = $parent->getId();
        } else {
            throw new InvalidParamError('parent', $parent, '$parent is expected to be an instance of ApplicationObject class of Class-ID pair');
        }

        $user_id = $user->getId();

        if ($notification_ids = DB::executeFirstColumn("SELECT notifications.id AS 'id' FROM notifications, notification_recipients WHERE notifications.id = notification_recipients.notification_id AND notifications.parent_type = ? AND notifications.parent_id = ? AND notification_recipients.recipient_id = ? AND notification_recipients.read_on IS NULL", $parent_type, $parent_id, $user_id)) {
            try {
                $cached_read_values = self::getReadCache($user, self::READ_CACHE_KEY);

                DB::beginWork('Marking parent notification as read @ ' . __CLASS__);

                foreach ($notification_ids as $notification_id) {
                    DB::execute('UPDATE notification_recipients SET read_on = UTC_TIMESTAMP() WHERE notification_id = ? AND recipient_id = ? AND read_on IS NULL', $notification_id, $user->getId());

                    if ($cached_read_values && is_array($cached_read_values)) {
                        $cached_read_values[$notification_id] = true;
                    }
                }

                DB::commit('Parent notification has been marked as read @ ' . __CLASS__);

                if (is_array($cached_read_values)) {
                    AngieApplication::cache()->setByObject($user, self::READ_CACHE_KEY, $cached_read_values);
                }
            } catch (Exception $e) {
                DB::rollback('Failed to mark parent notification as read @ ' . __CLASS__);
                throw $e;
            }
        }

        AngieApplication::cache()->removeByModel('notifications');
    }

    /**
     * Mark a single notification as unread.
     *
     * @param Notification $notification
     * @param User         $user
     */
    public static function markUnread(Notification $notification, User $user)
    {
        if (self::isRead($notification, $user, false, false)) {
            DB::execute('UPDATE notification_recipients SET read_on = NULL WHERE notification_id = ? AND recipient_id = ?', $notification->getId(), $user->getId());
            AngieApplication::cache()->removeByObject($notification);

            // Update read cache only if cache value exists (if not, system will rebuild it the first time it is needed)
            $cached_value = AngieApplication::cache()->getByObject($user, self::READ_CACHE_KEY);

            if (is_array($cached_value)) {
                $cached_value[$notification->getId()] = false;

                AngieApplication::cache()->setByObject($user, self::READ_CACHE_KEY, $cached_value);
            }
        }
    }

    /**
     * Mass-change read status for given user.
     *
     * @param User $user
     * @param      $new_read_status
     * @param bool $all_notifications
     * @param null $notification_ids
     *
     * @return array
     * @throws InvalidParamError
     */
    public static function updateReadStatusForRecipient(User $user, $new_read_status, $all_notifications = true, $notification_ids = null)
    {
        $new_read_on_value = $new_read_status ? 'UTC_TIMESTAMP()' : 'NULL';

        if ($all_notifications) {
            DB::execute("UPDATE notification_recipients SET read_on = $new_read_on_value WHERE recipient_id = ?", $user->getId());
        } else {
            if ($notification_ids) {
                DB::execute("UPDATE notification_recipients SET read_on = $new_read_on_value WHERE notification_id IN (?) AND recipient_id = ?", $notification_ids, $user->getId());
            } else {
                throw new InvalidParamError('notification_ids', $notification_ids, 'Missing notification ID-s');
            }
        }

        AngieApplication::cache()->removeByObject($user, self::READ_CACHE_KEY);

        return [];
    }

    /**
     * Returns true if $field_name is set to a non-null value for a given recipient and a given notification.
     *
     * This method is cache aware and it will maintain or rebuild cache if needed, based on provided parameters
     *
     * @param  Notification|int $notification
     * @param  User             $user
     * @param  bool             $use_cache
     * @param  bool             $rebuild_stale_cache
     * @return bool
     */
    private static function isReadTimestampSet($notification, User $user, $use_cache = true, $rebuild_stale_cache = true)
    {
        $notification_id = $notification instanceof Notification ? $notification->getId() : $notification;

        if (empty($use_cache) && empty($rebuild_stale_cache)) {
            return (bool) DB::executeFirstCell('SELECT COUNT(*) FROM notification_recipients WHERE notification_id = ? AND recipient_id = ? AND read_on IS NOT NULL', $notification_id, $user->getId());
        }

        $cached_values = self::getReadCache($user, self::READ_CACHE_KEY);

        return isset($cached_values[$notification_id]) && $cached_values[$notification_id];
    }

    /**
     * Get read cache.
     *
     * @param  User  $user
     * @return array
     */
    private static function getReadCache($user)
    {
        return AngieApplication::cache()->getByObject($user, self::READ_CACHE_KEY, function () use ($user) {
            $result = [];

            if ($rows = DB::execute('SELECT notification_id, read_on FROM notification_recipients WHERE recipient_id = ?', $user->getId())) {
                foreach ($rows as $row) {
                    $result[$row['notification_id']] = (bool) $row['read_on'];
                }
            }

            return $result;
        });
    }

    /**
     * Clear all notifications for a given recipient.
     *
     * @param  User              $user
     * @param  bool              $all_notifications
     * @param  array|int         $notification_ids
     * @throws InvalidParamError
     */
    public static function clearForRecipient(User $user, $all_notifications = true, $notification_ids = null)
    {
        if ($all_notifications) {
            DB::execute('DELETE FROM notification_recipients WHERE recipient_id = ?', $user->getId());
        } else {
            if ($notification_ids) {
                DB::execute('DELETE FROM notification_recipients WHERE notification_id IN (?) AND recipient_id = ?', $notification_ids, $user->getId());
            } else {
                throw new InvalidParamError('notification_ids', $notification_ids, 'Missing notification ID-s');
            }
        }

        AngieApplication::cache()->removeByObject($user, self::READ_CACHE_KEY);
    }

    // ---------------------------------------------------
    //  Utility methods
    // ---------------------------------------------------

    /**
     * Delete notifications by parent object.
     *
     * @param ApplicationObject $parent
     */
    public static function deleteByParent($parent)
    {
        if ($notification_ids = DB::executeFirstColumn('SELECT id FROM notifications WHERE ' . self::parentToCondition($parent))) {
            DB::execute('DELETE FROM notification_recipients WHERE notification_id IN (?)', $notification_ids);
            DB::execute('DELETE FROM notifications WHERE id IN (?)', $notification_ids);
        }
    }

    /**
     * Delete logged activitys by parent and additional property.
     *
     * @param ApplicationObject $parent
     * @param string            $property_name
     * @param mixed             $property_value
     */
    public static function deleteByParentAndAdditionalProperty($parent, $property_name, $property_value)
    {
        if ($rows = DB::execute('SELECT id, raw_additional_properties FROM notifications WHERE parent_type = ? AND parent_id = ?', get_class($parent), $parent->getId())) {
            $to_delete = [];

            foreach ($rows as $row) {
                if ($row['raw_additional_properties']) {
                    $properties = unserialize($row['raw_additional_properties']);

                    if (empty($properties[$property_name])) {
                        continue;
                    }

                    if (($property_value instanceof Closure && $property_value($properties[$property_name])) || $properties[$property_name] == $property_value) {
                        $to_delete[] = $row['id'];
                    }
                }
            }

            if (count($to_delete)) {
                DB::execute('DELETE FROM notification_recipients WHERE notification_id IN (?)', $to_delete);
                DB::execute('DELETE FROM notifications WHERE id IN (?)', $to_delete);
            }
        }
    }

    /**
     * Clean up old notifications.
     */
    public static function cleanUp()
    {
        if ($ids = DB::executeFirstColumn('SELECT id FROM notifications WHERE created_on < ?', DateValue::makeFromString('-30 days'))) {
            DB::transact(function () use ($ids) {
                DB::execute('DELETE FROM notification_recipients WHERE notification_id IN (?)', $ids);
                DB::execute('DELETE FROM notifications WHERE id IN (?)', $ids);
            }, 'Cleaning up old notifications');
        }
    }
}
