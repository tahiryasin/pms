<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * ActiveCollab specific model definition.
 *
 * @package ActiveCollab.resources
 */
class ActiveCollabModuleModel extends AngieModuleModel
{
    /**
     * Create a new company and return company ID.
     *
     * @param  string $name
     * @param  array  $additional
     * @return int
     */
    protected function addCompany($name, $additional = null)
    {
        $properties = ['name' => $name];

        if (is_array($additional)) {
            $properties = array_merge($properties, $additional);
        }

        $properties['created_on'] = date(DATETIME_MYSQL);
        if (!isset($properties['created_by_id'])) {
            $properties['created_by_id'] = 1;
        }

        $properties['updated_on'] = date(DATETIME_MYSQL);
        $properties['updated_by_id'] = $properties['created_by_id'];

        return $this->createObject('companies', $properties);
    }

    /**
     * Create a user and return user ID.
     *
     * @param  string $email
     * @param  int    $company_id
     * @param  array  $additional
     * @return int
     */
    protected function addUser($email, $company_id, $additional = null)
    {
        $properties = ['company_id' => $company_id, 'email' => $email];

        if (is_array($additional)) {
            $properties = array_merge($properties, $additional);
        }

        if (isset($properties['password'])) {
            $properties['password'] = password_hash(APPLICATION_UNIQUE_KEY . $properties['password'], PASSWORD_DEFAULT);
        } else {
            $properties['password'] = password_hash(APPLICATION_UNIQUE_KEY . 'test', PASSWORD_DEFAULT);
        }

        $properties['password_hashed_with'] = 'php';

        $properties['created_on'] = date(DATETIME_MYSQL);
        if (!isset($properties['created_by_id'])) {
            $properties['created_by_id'] = 1;
        }

        $properties['updated_on'] = date(DATETIME_MYSQL);

        return $this->createObject('users', $properties);
    }
}
