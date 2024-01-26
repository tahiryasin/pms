<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\UserInternalRateEvents\UserInternalRateCreatedEvent;
use ActiveCollab\Module\Tracking\Events\DataObjectLifeCycleEvents\UserInternalRateEvents\UserInternalRateUpdatedEvent;

/**
 * UserInternalRates class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class UserInternalRates extends BaseUserInternalRates
{
    /**
     * Return new collection.
     *
     * Possibilities:
     *
     * - all_for_1 (where 1 is user ID)
     *
     * @param  string            $collection_name
     * @param                    $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if (str_starts_with($collection_name, 'user_internal_rates_for_')) {
            $bits = explode('_', $collection_name);
            $internal_rates_for_id = (int) array_pop($bits);

            $collection->setConditions('user_id = ?', $internal_rates_for_id);
        } elseif ($collection_name !== DataManager::ALL) {
            throw new InvalidParamError('collection_name', $collection_name);
        }

        return $collection;
    }

    /**
     * Return existing hourly rate by attributes (user_id, user_name, user_email and date).
     *
     * @return DataObject|null
     * @throws DBQueryError
     * @throws InvalidParamError
     */
    public static function getExistingHourlyRateByAttributes(array $attributes): ?UserInternalRate
    {
        return self::findOneBy(
            [
                'user_id' => array_var($attributes, 'user_id'),
                'valid_from' => array_var($attributes, 'valid_from'),
             ]
        );
    }

    public static function getCurrent(int $userId): ?UserInternalRate
    {
        return UserInternalRates::findOneBySql('SELECT * FROM user_internal_rates WHERE user_id = ? AND valid_from <= CURRENT_DATE() ORDER BY valid_from DESC', $userId);
    }

    public static function getByDate(int $userId, DateValue $date)
    {
        /** @var UserInternalRate|null $firstInternalRate */
        $firstInternalRate = UserInternalRates::findOneBySql('SELECT * FROM user_internal_rates WHERE user_id = ? ORDER BY valid_from ASC', $userId);
        if ($firstInternalRate && $date < $firstInternalRate->getValidFrom()) {
            return $firstInternalRate;
        }

        return UserInternalRates::findOneBySql('SELECT * FROM user_internal_rates WHERE user_id = ? AND valid_from <= ? ORDER BY valid_from DESC', $userId, $date->toMySQL());
    }

    public static function &update(DataObject &$instance, array $attributes, $save = true): UserInternalRate
    {
        /** @var UserInternalRate $user_internal_rate */
        $user_internal_rate = parent::update($instance, $attributes, $save);
        AngieApplication::eventsDispatcher()->trigger(new UserInternalRateUpdatedEvent($user_internal_rate));

        return $user_internal_rate;
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true): UserInternalRate
    {
        /** @var UserInternalRate $user_internal_rage */
        $user_internal_rage = parent::create($attributes, $save, $announce);
        AngieApplication::eventsDispatcher()->trigger(new UserInternalRateCreatedEvent($user_internal_rage));

        return $user_internal_rage;
    }

    public static function scrap(DataObject &$instance, $force_delete = false)
    {
        return parent::scrap($instance, $force_delete);
    }
}
