<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents SET database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBSetColumn extends DBColumn
{
    /**
     * Enum possibilities.
     *
     * @var array
     */
    private $possibilities = [];

    /**
     * Construct set field.
     *
     * @param string $name
     * @param array  $possibilities
     * @param mixed  $default
     */
    public function __construct($name, $possibilities = [], $default = null)
    {
        parent::__construct($name, $default);
        $this->possibilities = $possibilities;
    }

    /**
     * Construct and return set column instance.
     *
     * @param  string      $name
     * @param  array       $possibilities
     * @param  array       $default
     * @return DBSetColumn
     */
    public static function create($name, $possibilities = [], $default = null)
    {
        return new self($name, $possibilities, $default);
    }

    /**
     * Process additional field parameters.
     *
     * @param array $additional
     */
    public function processAdditional($additional)
    {
        parent::processAdditional($additional);

        if (is_array($additional) && isset($additional[0]) && $additional[0]) {
            $this->possibilities = $additional;
        }
    }

    /**
     * Returns prepared default value.
     *
     * @return string
     */
    public function prepareDefault()
    {
        if (is_array($this->default)) {
            return "'" . implode(',', $this->default) . "'";
        } elseif ($this->default === null) {
            return 'NULL';
        } else {
            return '';
        }
    }

    /**
     * Prepare type definition.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        $possibilities = [];
        foreach ($this->possibilities as $v) {
            $possibilities[] = var_export((string) $v, true);
        }

        return 'set(' . implode(', ', $possibilities) . ')';
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        $possibilities = [];

        foreach ($this->getPossibilities() as $v) {
            $possibilities[] = var_export($v, true);
        }

        $possibilities = 'array(' . implode(', ', $possibilities) . ')';

        $default = $this->getDefault() === null ? '' : ', ' . var_export($this->getDefault(), true);

        return "DBSetColumn::create('" . $this->getName() . "', $possibilities$default)";
    }

    /**
     * Return possibilities.
     *
     * @return array
     */
    public function getPossibilities()
    {
        return $this->possibilities;
    }

    // ---------------------------------------------------
    //  Model generator
    // ---------------------------------------------------

    /**
     * Set possibilities value.
     *
     * @param  array       $value
     * @return DBSetColumn
     */
    public function &setPossibilities($value)
    {
        $this->possibilities = $value;

        return $this;
    }

    /**
     * Load from row.
     *
     * @param array $row
     */
    public function loadFromRow($row)
    {
        parent::loadFromRow($row);

        if (isset($row['Default']) && $row['Default']) {
            $this->setDefault(explode(',', $row['Default']));
        }
    }

    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------

    /**
     * Return verbose PHP type.
     *
     * @return string
     */
    public function getPhpType()
    {
        return 'mixed';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return '$value';
    }
}
