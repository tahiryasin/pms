<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Companies manager class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Companies extends BaseCompanies
{
    /**
     * Cached owner company ID, so it's easily access for User::isSubcontractor() and similar checks.
     *
     * @var int
     */
    private static $owner_company_id = false;

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
        if ($user instanceof User) {
            $collection = parent::prepareCollection($collection_name, $user);

            $collection->setPreExecuteCallback(function ($ids) {
                Users::preloadMemberIdsByField('Company', $ids, 'company_id');
            });

            switch ($collection_name) {
                case self::ALL:
                    $collection->setConditions('id IN (?)', $user->getVisibleCompanyIds());
                    break;
                case self::ACTIVE:
                    $collection->setConditions('id IN (?) AND is_archived = ? AND is_trashed = ?', $user->getVisibleCompanyIds(), false, false);
                    break;
                case self::ARCHIVED:
                    $collection->setConditions('id IN (?) AND is_archived = ? AND is_trashed = ?', $user->getVisibleCompanyIds(), true, false);
                    break;
                default:
                    throw new InvalidParamError('collection_name', $collection_name);
            }

            return $collection;
        } else {
            throw new InvalidParamError('user', $user, '$user is required to be a user');
        }
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $company = parent::create($attributes, $save, false);

        if ($company instanceof Company && $company->isLoaded()) {
            if (array_key_exists('hourly_rates', $attributes)) {
                $company->setHourlyRates($attributes['hourly_rates']);
            }
        }

        Users::clearCache();

        return DataObjectPool::announce($company, DataObjectPool::OBJECT_CREATED, $attributes);
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Update an instance.
     *
     * @param  DataObject           $instance
     * @param  array                $attributes
     * @param  bool                 $save
     * @return DataObject
     * @throws InvalidInstanceError
     * @throws Exception
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        if ($instance instanceof Company) {
            try {
                DB::beginWork('Begin: updating company @ ' . __CLASS__);

                parent::update($instance, $attributes, $save);

                if ($save && array_key_exists('hourly_rates', $attributes)) {
                    $instance->setHourlyRates($attributes['hourly_rates']);
                }

                DB::commit('Done: updating company @ ' . __CLASS__);
            } catch (Exception $e) {
                DB::rollback('Rollback: updating company @ ' . __CLASS__);
                throw $e;
            }
        } else {
            throw new InvalidInstanceError('instance', $instance, 'Company');
        }

        return $instance;
    }

    /**
     * Can $user create a new company.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isPowerUser();
    }

    // ---------------------------------------------------
    //  Finders
    // ---------------------------------------------------

    /**
     * Returns true if $user can see company notes.
     *
     * @param  User $user
     * @return bool
     */
    public static function canSeeNotes(User $user)
    {
        return $user->isMember();
    }

    /**
     * Find company by its name.
     *
     * @param  string  $name
     * @return Company
     */
    public static function findByName($name)
    {
        return self::find(['conditions' => ['name = ?', $name], 'one' => true]);
    }

    /**
     * Find active companies visible to $user.
     *
     * @param  User  $user
     * @return array
     */
    public static function findActive(User $user)
    {
        $visible_ids = $user->getVisibleCompanyIds();

        if ($visible_ids) {
            return self::find([
                'conditions' => ['((is_archived = ? AND is_trashed = ?) OR id = ?) AND id IN (?)', false, false, $user->getCompanyId(), $visible_ids],
                'order' => 'is_owner DESC, name',
            ]);
        }

        return false;
    }

    /**
     * Return owner company from database.
     *
     * @return Company
     */
    public static function findOwnerCompany()
    {
        return DataObjectPool::get(Company::class, self::getOwnerCompanyId());
    }

    /**
     * Return owner company ID.
     *
     * @return int
     */
    public static function getOwnerCompanyId()
    {
        if (self::$owner_company_id === false) {
            self::$owner_company_id = AngieApplication::cache()->get(['models', 'companies', 'owner_company_id'], function () {
                return (int) DB::executeFirstCell('SELECT id FROM companies WHERE is_owner = ? LIMIT 0, 1', true);
            });
        }

        return self::$owner_company_id;
    }

    /**
     * Return ID => name map.
     *
     * @param  mixed $ids
     * @param  int   $min_state
     * @return array
     */
    public static function getIdNameMap($ids = null, $min_state = STATE_TRASHED)
    {
        // No ID-s
        if ($ids === null) {
            return AngieApplication::cache()->get(['models', 'companies', 'id_name_map', $min_state], function () use ($min_state) {
                switch ($min_state) {
                    case STATE_TRASHED:
                        $conditions = '';
                        break;
                    case STATE_ARCHIVED:
                        $conditions = DB::prepare('WHERE is_trashed = ?', false);
                        break;
                    default:
                        $conditions = DB::prepare('WHERE is_archived AND is_trashed = ?', false, false);
                        break;
                }

                if ($rows = DB::execute('SELECT id, name FROM companies ' . $conditions . ' ORDER BY is_owner DESC, name')) {
                    $result = [];

                    foreach ($rows as $row) {
                        $result[$row['id']] = $row['name'];
                    }

                    return $result;
                }

                return null;
            });

            // We have ID-s
        } else {
            if (is_foreachable($ids)) {
                $from_cache = self::getIdNameMap(null, $min_state);

                if ($from_cache) {
                    foreach ($from_cache as $k => $v) {
                        if (!in_array($k, $ids)) {
                            unset($from_cache[$k]);
                        }
                    }
                }

                return $from_cache;
            }

            return null;
        }
    }

    /**
     * Return ID note map for companies.
     *
     * @return array|null
     */
    public static function getIdNoteMap()
    {
        return AngieApplication::cache()->get(['models', 'companies', 'id_note_map'], function () {
            $result = [];

            if ($rows = DB::execute('SELECT id, note FROM companies WHERE note > "" ORDER BY id')) {
                foreach ($rows as $row) {
                    $result[$row['id']] = $row['note'];
                }
            }

            return $result;
        });
    }

    /**
     * Return id and name by given set of project IDs.
     *
     * @param  array $ids
     * @return array
     */
    public static function getIdNameMapByProjectIds(array $project_ids)
    {
        $result = [];

        if ($project_ids && $rows = DB::execute("SELECT companies.id AS 'client_id', companies.name AS 'client_name', projects.id AS 'project_id' FROM companies LEFT JOIN projects ON companies.id = projects.company_id WHERE projects.id IN (?)", $project_ids)) {
            foreach ($rows as $row) {
                $result[$row['project_id']] = ['client_id' => $row['client_id'], 'client_name' => $row['client_name']];
            }
        }

        return $result;
    }

    /**
     * Return true if $user is last owner in the company.
     *
     * @param  Company $company
     * @return bool
     */
    public static function isLastOwnerInCompany(Company $company)
    {
        if (Users::count(["type = 'Owner' AND is_archived = ? AND is_trashed = ?", false, false]) === 1) {
            return DB::executeFirstCell("SELECT company_id FROM users WHERE type = 'Owner' AND is_archived = ? AND is_trashed = ? LIMIT 0, 1", false, false) === $company->getId();
        }

        return false;
    }

    /**
     * Return true if company has owners.
     *
     * @param  Company $company
     * @return bool
     */
    public static function hasOwners(Company $company)
    {
        return (bool) Users::count(
            [
                '`type` = ? AND `is_archived` = ? AND `is_trashed` = ? AND `company_id` = ?',
                Owner::class,
                false,
                false,
                $company->getId(),
            ]
        );
    }

    /**
     * Touch batch of companies identified by an array od ids.
     *
     * @param  array             $ids
     * @throws InvalidParamError
     */
    public static function batchTouch(array $ids)
    {
        if (!empty($ids) && ($user = AngieApplication::authentication()->getLoggedUser())) {
            DB::execute(
                'UPDATE companies SET updated_on = ?, updated_by_id = ?, updated_by_name = ?, updated_by_email = ? WHERE id IN (?)',
                DateTimeValue::now(),
                $user->getId(),
                $user->getDisplayName(true),
                $user->getEmail(),
                $ids
            );
        }
    }
}
