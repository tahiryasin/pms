<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\AvailabilityRecordEvents\AvailabilityRecordCreatedEvent;
use ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\AvailabilityRecordEvents\AvailabilityRecordDeletedEvent;
use ActiveCollab\Module\System\Events\DataObjectLifeCycleEvents\AvailabilityTypeEvents\AvailabilityTypeUpdatedEvent;

/**
 * AvailabilityRecords class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class AvailabilityRecords extends BaseAvailabilityRecords
{
    public static function prepareCollection($collection_name, $user)
    {
        $collection = new AvailabilityRecordsCollection($collection_name, AvailabilityRecords::class);

        $collection->setOrderBy('end_date DESC');

        if (str_starts_with($collection_name, 'availability_records_for_user')) {
            self::prepareUserRecordsCollection($collection, $collection_name);
        } elseif (str_starts_with($collection_name, 'all_availability_records_from')) {
            self::prepareAllUsersRecordsCollection($collection, $collection_name, $user);
        }

        return $collection;
    }

    private static function prepareUserRecordsCollection(ModelCollection &$collection, $collection_name)
    {
        $bits = explode('_', $collection_name);
        $date = new DateValue(array_pop($bits));
        array_pop($bits); // date
        $scope = array_pop($bits);
        array_pop($bits); // scope
        $user_id = array_pop($bits);

        $conditions = [DB::prepare('user_id = ?', $user_id)];

        if ($scope === 'previous') {
            $conditions[] = DB::prepare('end_date < ?', $date);
        } elseif ($scope === 'current') {
            $conditions[] = DB::prepare('end_date >= ?', $date);
        }

        $collection->setConditions(implode(' AND ', $conditions));
    }

    private static function prepareAllUsersRecordsCollection(
      ModelCollection &$collection,
      $collection_name,
      User $user
    )
    {
        $bits = explode('_', $collection_name);
        $start_date = new DateValue(array_pop($bits));
        array_pop($bits); // to
        $end_date = new DateValue(array_pop($bits));

        $dates_conditions[] = DB::prepare(
            '(start_date >= ? AND end_date <= ?)',
            $start_date,
            $end_date
        );

        $dates_conditions[] = DB::prepare(
            '(start_date < ? AND end_date > ?)',
            $start_date,
            $end_date
        );

        $conditions[] = '(' . implode(' OR ', $dates_conditions) . ')';
        $conditions[] = DB::prepare(
            'user_id IN (?)',
            $user->getVisibleUserIds()
        );

        $collection->setConditions(implode(' AND ', $conditions));
    }

    public static function canAdd(User $user)
    {
        return $user->isMember();
    }

    public static function createAvailability(User $by, User $for, array $data): AvailabilityRecord
    {
        if (!$for->isActive()) {
            throw new LogicException(
                lang('Cannot manage availability for archived or trashed user.')
            );
        }

        if (!$for->canAddAvailabilityRecord($by)) {
            throw new InsufficientPermissionsError(
                lang('Cannot manage availability for this user.')
            );
        }

        /** @var AvailabilityRecord $availability_record */
        $availability_record = parent::create(
            [
                'availability_type_id' => $data['availability_type_id'],
                'user_id' => $for->getId(),
                'message' => $data['message'],
                'start_date' => new DateValue($data['start_date']),
                'end_date' => new DateValue($data['end_date']),
            ],
            false
        );
        $availability_record->setCreatedBy($by);
        $availability_record->save();

        $availability_type = $availability_record->getAvailabilityType();

        $availability_type->touch();

        DataObjectPool::announce(new AvailabilityRecordCreatedEvent($availability_record));
        DataObjectPool::announce(new AvailabilityTypeUpdatedEvent($availability_type));

        return $availability_record;
    }

    public static function removeAvailability(AvailabilityRecord $availability_record, User $by)
    {
        if (!$availability_record->canDelete($by)) {
            throw new InsufficientPermissionsError(
                lang('Cannot delete availability for this user.')
            );
        }

        $user = $availability_record->getUser();
        $availability_type = $availability_record->getAvailabilityType();

        if ($user->getId() !== $by->getId()) {
            AngieApplication::notifications()
                ->notifyAbout(
                    'system/availability_record_deleted',
                    $availability_record,
                    $by
                )
                ->sendToUsers($user);
        }

        AvailabilityRecords::scrap($availability_record);

        $availability_type->touch();

        DataObjectPool::announce(new AvailabilityRecordDeletedEvent($availability_record));
        DataObjectPool::announce(new AvailabilityTypeUpdatedEvent($availability_type));
    }
}
