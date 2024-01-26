<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents VARCHAR database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBStringColumn extends DBColumn
{
    const MAX_LENGTH = 191;

    /**
     * Field length (max is 191).
     *
     * @var int
     */
    protected $length = self::MAX_LENGTH;

    /**
     * Construct string column.
     *
     * @param string $name
     * @param int    $lenght
     * @param mixed  $default
     */
    public function __construct($name, $lenght = self::MAX_LENGTH, $default = null)
    {
        parent::__construct($name, $default);

        $this->length = (int) $lenght;

        if ($this->length > self::MAX_LENGTH) {
            $this->length = self::MAX_LENGTH;
        }
    }

    /**
     * Create new integer column instance.
     *
     * @param  string         $name
     * @param  int            $lenght
     * @param  mixed          $default
     * @return DBStringColumn
     */
    public static function create($name, $lenght = self::MAX_LENGTH, $default = null)
    {
        return new self($name, $lenght, $default);
    }

    /**
     * Process additional field properties.
     *
     * @param array $additional
     */
    public function processAdditional($additional)
    {
        parent::processAdditional($additional);

        if (is_array($additional) && isset($additional[0]) && $additional[0]) {
            $this->length = (int) $additional[0];
        }
    }

    /**
     * Return type definition.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        return "varchar($this->length)";
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        if ($this->getName() == 'name') {
            return 'DBNameColumn::create(' . $this->getLength() . ')';
        } elseif ($this->getName() == 'type') {
            if ($this->getDefault() === null) {
                return 'DBTypeColumn::create()';
            } else {
                return 'DBTypeColumn::create(' . var_export($this->getDefault(), true) . ')';
            }
        } else {
            $default = $this->getDefault() === null ? '' : ', ' . var_export($this->getDefault(), true);

            return "DBStringColumn::create('" . $this->getName() . "', " . $this->getLength() . "$default)";
        }
    }

    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------

    /**
     * Return length.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set string field lenght.
     *
     * @param  int            $value
     * @return DBStringColumn
     */
    public function &setLength($value)
    {
        $this->length = (int) $value;

        if ($this->length > self::MAX_LENGTH) {
            $this->length = self::MAX_LENGTH;
        }

        return $this;
    }
}
