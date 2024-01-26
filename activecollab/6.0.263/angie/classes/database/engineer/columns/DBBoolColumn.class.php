<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents BOOL database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBBoolColumn extends DBColumn
{
    public static function create($name, $default = false)
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
        return 'tinyint(1) unsigned';
    }

    /**
     * Prepare NULL part of type definition.
     *
     * @return string
     */
    public function prepareNull()
    {
        return 'NOT NULL';
    }

    /**
     * Prepare default value definition.
     *
     * @return string
     */
    public function prepareDefault()
    {
        return $this->default ? "'1'" : "'0'";
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        if ($this->getDefault() === null) {
            $default = '';
        } else {
            $default = $this->getDefault() ? ', true' : ', false';
        }

        return "DBBoolColumn::create('" . $this->getName() . "'$default)";
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
        return 'bool';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return '(bool) $value';
    }
}
