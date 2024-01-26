<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents DATETIME database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBDateTimeColumn extends DBColumn
{
    /**
     * Create new column instance.
     *
     * @param  string           $name
     * @param  mixed            $default
     * @return DBDateTimeColumn
     */
    public static function create($name, $default = null)
    {
        return new self($name, $default);
    }

    /**
     * Prepare type definition.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        return 'datetime';
    }

    /**
     * Prepare default value.
     *
     * @return string
     */
    public function prepareDefault()
    {
        if ($this->default === null) {
            return 'NULL';
        } else {
            return is_int($this->default) ? "'" . date(DATETIME_MYSQL, $this->default) . "'" : "'" . date(DATETIME_MYSQL, strtotime($this->default)) . "'";
        }
    }

    /**
     * Return verbose PHP type.
     *
     * @return string
     */
    public function getPhpType()
    {
        return 'DateTimeValue';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return 'datetimeval($value)';
    }
}
