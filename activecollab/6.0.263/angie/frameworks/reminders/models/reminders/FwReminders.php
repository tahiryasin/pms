<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Globalization;

/**
 * Framework level reminder management implementation.
 *
 * @package angie.frameworks.reminders
 * @subpackage models
 */
abstract class FwReminders extends BaseReminders
{
    /**
     * Return new collection.
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'reminders_for')) {
            [$parent, $for] = Reminders::parentAndUserFromCollectionName($collection_name);

            if ($parent instanceof IReminders && $for instanceof User && $for->getId() === $user->getId()) {
                $collection = parent::prepareCollection($collection_name, $user);
                $collection->setConditions('parent_type = ? AND parent_id = ? AND created_by_id = ?', get_class($parent), $parent->getId(), $for->getId());

                return $collection;
            }
        }

        throw new InvalidParamError('collection_name', $collection_name);
    }

    /**
     * Get parent and user from reminders collection name.
     *
     * @param  string       $collection_name
     * @return DataObject[]
     */
    private static function parentAndUserFromCollectionName($collection_name)
    {
        $bits = explode('_', $collection_name);

        [$parent_type, $parent_id] = explode('-', array_pop($bits));

        if ($parent_type && $parent_id) {
            $parent = DataObjectPool::get($parent_type, $parent_id);
        } else {
            $parent = null;
        }

        array_pop($bits); // Remove _in_

        return [$parent, DataObjectPool::get('User', array_pop($bits))];
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        try {
            DB::beginWork('Creating a reminder');

            /** @var Reminder $reminder */
            $reminder = parent::create($attributes, $save, $announce); // @TODO Announcement should be sent after we verify that reminder has subscribers

            if ($reminder->isLoaded() && !$reminder->countSubscribers()) {
                throw new ValidationErrors(
                    [
                        'subscribers' => 'No subscribers specified',
                    ]
                );
            }

            DB::commit('Reminder created');

            return $reminder;
        } catch (Exception $e) {
            DB::rollback('Failed to create a reminder');
            throw $e;
        }
    }

    /**
     * Send reminers for today.
     */
    public static function send()
    {
        $today = DateValue::now();
        $offset = Globalization::getGmtOffset();

        if ($offset != 0) {
            $today->advance($offset, true);
        }

        if ($reminders = Reminders::findDueForSend($today)) {
            foreach ($reminders as $reminder) {
                // don't send reminder for closed task, only delete
                if ($reminder->getParent() instanceof Task && Tasks::findById($reminder->getParentId())->isCompleted()) {
                    $reminder->delete();
                    continue;
                }

                $reminder->send();
                $reminder->delete();
            }
        }
    }

    /**
     * Return all reminders that need to be send on the given date.
     *
     * @param  DateValue           $date
     * @return Reminder[]|DBResult
     */
    public static function findDueForSend(DateValue $date)
    {
        return Reminders::findBy('send_on', $date);
    }

    /**
     * Drop all reminders by user.
     *
     * @param  User                 $user
     * @throws InvalidInstanceError
     */
    public static function deleteByUser(User $user)
    {
        if ($user instanceof User) {
            DB::transact(function () use ($user) {
                if ($reminders = Reminders::findBy('created_by_id', $user->getId())) {
                    foreach ($reminders as $reminder) {
                        $reminder->delete();
                    }
                }
            });
        } else {
            throw new InvalidInstanceError('user', $user, 'User');
        }
    }
}
