<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents DATE database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBDateColumn extends DBColumn
{
    /**
     * Create new column instance.
     *
     * @param  string       $name
     * @param  mixed        $default
     * @return DBDateColumn
     */
    public static function create($name, $default = null)
    {
        return new self($name, $default);
    }

    /**
     * Return type definition.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        return 'date';
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
            return is_int($this->default) ? "'" . date(DATE_MYSQL, $this->default) . "'" : "'" . date(DATE_MYSQL, strtotime($this->default)) . "'";
        }
    }

    // ---------------------------------------------------
    //  Model generator
    // ---------------------------------------------------

    /**
     * Return verbose PHP type.
     *
     * @return string
     */
    public function getPhpType()
    {
        return 'DateValue';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return 'dateval($value)';
    }
}
