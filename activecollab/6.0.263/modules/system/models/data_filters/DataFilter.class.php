<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Application level data filter class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class DataFilter extends FwDataFilter
{
    const USER_FILTER_COMPANY_MEMBER = 'company';

    /**
     * Returns true if $value is a valid and supported user filter.
     *
     * @param  string $value
     * @return bool
     */
    protected function isValidUserFilter($value)
    {
        if ($value == self::USER_FILTER_COMPANY_MEMBER) {
            return true;
        }

        return parent::isValidUserFilter($value);
    }

    /**
     * Prepare and return user filter getter method names.
     *
     * @param  string $user_filter_name
     * @return array
     */
    protected function prepareUserFilterGetterNames($user_filter_name)
    {
        return array_merge(parent::prepareUserFilterGetterNames($user_filter_name), ['get' . Angie\Inflector::camelize($user_filter_name) . 'ByCompanyMember']);
    }

    /**
     * Prepare and return user filter setter method names.
     *
     * @param  string $user_filter_name
     * @return array
     */
    protected function prepareUserFilterSetterNames($user_filter_name)
    {
        return array_merge(parent::prepareUserFilterSetterNames($user_filter_name), [lcfirst(Angie\Inflector::camelize($user_filter_name)) . 'ByCompanyMember']);
    }

    /**
     * Set filter attributes.
     *
     * @param  string            $user_filter_name
     * @param  string            $value
     * @throws InvalidParamError
     */
    protected function setUserFilterAttribute($user_filter_name, $value)
    {
        if (str_starts_with($value, self::USER_FILTER_COMPANY_MEMBER)) {
            call_user_func([$this, $this->getUserFilterSetters($user_filter_name)[2]], $this->getIdsFromFilterValue($value));
        } else {
            parent::setUserFilterAttribute($user_filter_name, $value);
        }
    }

    /**
     * Describe user filter.
     *
     * @param string $user_filter_name
     * @param array  $result
     */
    protected function describeUserFilter($user_filter_name, &$result)
    {
        parent::describeUserFilter($user_filter_name, $result);

        $user_filter_getters = $this->getUserFilterGetters($user_filter_name);

        $filter_getter = $user_filter_getters[0];
        $company_member_getter = $user_filter_getters[2];

        if ($this->$filter_getter() == self::USER_FILTER_COMPANY_MEMBER) {
            $result["{$user_filter_name}_by_company_member_id"] = call_user_func([$this, $company_member_getter]);
        }
    }

    /**
     * Prepare conditions for a particular user filter.
     *
     * @param  User                      $user
     * @param  string                    $user_filter_name
     * @param  string                    $table_name
     * @param  array                     $conditions
     * @param  string                    $field_name
     * @throws DataFilterConditionsError
     */
    protected function prepareUserFilterConditions(User $user, $user_filter_name, $table_name, &$conditions, $field_name = null)
    {
        $user_filter_getters = $this->getUserFilterGetters($user_filter_name);

        $filter_getter = $user_filter_getters[0];
        $company_member_getter = $user_filter_getters[2];

        if ($this->$filter_getter() == self::USER_FILTER_COMPANY_MEMBER) {
            $user_filter = $this->getFilterByUserFilterName($user_filter_name);
            $field_name = $this->getUserFilterFieldName($field_name, $user_filter_name);

            $full_field_name = $table_name ? "`$table_name`.`$field_name`" : "`$field_name`";

            $company_ids = $this->$company_member_getter();

            if (!empty($company_ids)) {
                if ($companies = Companies::findByIds((array) $company_ids)) {
                    $visible_user_ids = [];

                    foreach ($companies as $company) {
                        if ($visible_company_user_ids = $user->getVisibleUserIds($company)) {
                            $visible_user_ids = array_merge($visible_user_ids, $visible_company_user_ids);
                        }
                    }

                    if (count($visible_user_ids)) {
                        $conditions[] = DB::prepare("($full_field_name IN (?))", array_unique($visible_user_ids));
                    } else {
                        throw new DataFilterConditionsError($user_filter, self::USER_FILTER_COMPANY_MEMBER, $company_ids, "User can't see any members of these companies");
                    }
                } else {
                    throw new DataFilterConditionsError($user_filter, self::USER_FILTER_COMPANY_MEMBER, $company_ids, 'No companies found');
                }
            } else {
                throw new DataFilterConditionsError($user_filter, self::USER_FILTER_COMPANY_MEMBER, $company_ids, 'No company selected');
            }
        } else {
            parent::prepareUserFilterConditions($user, $user_filter_name, $table_name, $conditions, $field_name);
        }
    }
}
