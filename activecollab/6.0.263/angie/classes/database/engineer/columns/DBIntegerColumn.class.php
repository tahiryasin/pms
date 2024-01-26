<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents INT database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBIntegerColumn extends DBNumericColumn
{
    /**
     * Is this field auto - increment.
     *
     * @var bool
     */
    private $auto_increment = false;

    /**
     * Construct numeric column.
     *
     * @param string                $name
     * @param int|mixed|string|null $lenght
     * @param mixed                 $default
     */
    public function __construct($name, $lenght = DBColumn::NORMAL, $default = null)
    {
        if ($default !== null) {
            $default = (int) $default;
        }

        parent::__construct($name, $lenght, $default);
    }

    /**
     * Create new integer column instance.
     *
     * @param  string          $name
     * @param  int             $lenght
     * @param  mixed           $default
     * @return DBIntegerColumn
     */
    public static function create($name, $lenght = 5, $default = null)
    {
        return new self($name, $lenght, $default);
    }

    /**
     * Load column details from row.
     *
     * @param array $row
     */
    public function loadFromRow($row)
    {
        parent::loadFromRow($row);
        $this->auto_increment = isset($row['Extra']) && $row['Extra'] == 'auto_increment';
    }

    /**
     * Prepare definition.
     *
     * @return string
     */
    public function prepareDefinition()
    {
        return $this->auto_increment ? parent::prepareDefinition() . ' auto_increment' : parent::prepareDefinition();
    }

    /**
     * Prepare type defintiion.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        $result = $this->length ? "int($this->length)" : 'int';
        if ($this->unsigned) {
            $result .= ' unsigned';
        }

        return $this->size == DBColumn::NORMAL ? $result : $this->size . $result;
    }

    /**
     * Prepare null.
     *
     * @return string
     */
    public function prepareNull()
    {
        return $this->auto_increment || $this->default !== null ? 'NOT NULL' : 'NULL';
    }

    /**
     * Prepare default value.
     *
     * @return string
     */
    public function prepareDefault()
    {
        if ($this->auto_increment) {
            return ''; // no default for auto increment columns
        } else {
            return parent::prepareDefault();
        }
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        if ($this->getName() == 'id') {
            $length = '';

            switch ($this->getSize()) {
                case DBColumn::TINY:
                    $length .= 'DBColumn::TINY';
                    break;
                case DBColumn::SMALL:
                    $length .= 'DBColumn::SMALL';
                    break;
                case DBColumn::MEDIUM:
                    $length .= 'DBColumn::MEDIUM';
                    break;
                case DBColumn::BIG:
                    $length .= 'DBColumn::BIG';
                    break;
            }

            return "DBIdColumn::create($length)";
        } else {
            $default = $this->getDefault() === null ? '' : ', ' . var_export($this->getDefault(), true);

            $result = "DBIntegerColumn::create('" . $this->getName() . "', " . $this->getLength() . "$default)";

            if ($this->unsigned) {
                $result .= '->setUnsigned(true)';
            }

            if ($this->auto_increment) {
                $result .= '->setAutoIncrement(true)';
            }

            return $result;
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
        return 'int';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return '(int) $value';
    }

    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------

    /**
     * Return auto_increment.
     *
     * @return bool
     */
    public function getAutoIncrement()
    {
        return $this->auto_increment;
    }

    /**
     * Set auto increment flag value.
     *
     * @param  bool            $value
     * @return DBIntegerColumn
     */
    public function &setAutoIncrement($value)
    {
        $this->auto_increment = (bool) $value;

        return $this;
    }
}
