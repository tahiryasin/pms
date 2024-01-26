<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents TIME database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBTimeColumn extends DBColumn
{
    /**
     * Create and return tme column.
     *
     * @param  string       $name
     * @param  mixed        $default
     * @return DBTimeColumn
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
        return 'time';
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
        return 'DateTimeValue';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return 'timeval($value)';
    }
}
