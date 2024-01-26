<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents ENUM database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBEnumColumn extends DBColumn
{
    /**
     * Enum possibilities.
     *
     * @var array
     */
    private $possibilities = [];

    /**
     * Construct enum column.
     *
     * @param string $name
     * @param array  $possibilities
     * @param mixed  $default
     */
    public function __construct($name, $possibilities = null, $default = null)
    {
        parent::__construct($name, $default);

        if (is_array($possibilities)) {
            $this->possibilities = $possibilities;
        }
    }

    /**
     * Create new column instance.
     *
     * @param  string       $name
     * @param  array        $possibilities
     * @param  mixed        $default
     * @return DBEnumColumn
     */
    public static function create($name, $possibilities = null, $default = null)
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

        return 'enum(' . implode(', ', $possibilities) . ')';
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

        return "DBEnumColumn::create('" . $this->getName() . "', $possibilities$default)";
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
    //  Getters and setters
    // ---------------------------------------------------

    /**
     * Set array of possibilities.
     *
     * @param  array        $value
     * @return DBEnumColumn
     */
    public function &setPossibilities($value)
    {
        $this->possibilities = $value;

        return $this;
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return '(empty($value) ? null : (string) $value)';
    }
}
