<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents FLOAT/DOUBLE database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBFloatColumn extends DBNumericColumn
{
    /**
     * Column scale.
     *
     * @var int
     */
    private $scale = 2;

    /**
     * Construct float column.
     *
     * @param string $name
     * @param int    $lenght
     * @param int    $scale
     * @param mixed  $default
     */
    public function __construct($name, $lenght = 12, $scale = 2, $default = null)
    {
        if ($default !== null) {
            $default = (float) $default;
        }

        parent::__construct($name, $lenght, $default);

        $this->scale = (int) $scale;
    }

    /**
     * Create and return float column.
     *
     * @param  string        $name
     * @param  int           $lenght
     * @param  int           $scale
     * @param  mixed         $default
     * @return DBFloatColumn
     */
    public static function create($name, $lenght = 12, $scale = 2, $default = null)
    {
        return new self($name, $lenght, $scale, $default);
    }

    /**
     * Process additional field parameters.
     *
     * @param array $additional
     */
    public function processAdditional($additional)
    {
        parent::processAdditional($additional);

        if (is_array($additional) && isset($additional[1]) && $additional[1]) {
            $this->scale = (int) $additional[1];
        }
    }

    /**
     * Prepare type definition.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        $result = 'float(' . $this->length . ', ' . $this->scale . ')';
        if ($this->unsigned) {
            $result .= ' unsigned';
        }

        return $result;
    }

    /**
     * Prepare default value.
     *
     * @return string
     */
    public function prepareDefault()
    {
        return parent::prepareDefault() === null ? null : (float) parent::prepareDefault();
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        $default = $this->getDefault() === null ? '' : ', ' . var_export($this->getDefault(), true);

        $result = "DBFloatColumn::create('" . $this->getName() . "', " . $this->getLength() . ', ' . $this->getScale() . "$default)";

        if ($this->unsigned) {
            $result .= '->setUnsigned(true)';
        }

        return $result;
    }

    // ---------------------------------------------------
    //  Model generator
    // ---------------------------------------------------

    /**
     * Return scale.
     *
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Set scale.
     *
     * @param  int           $value
     * @return DBFloatColumn
     */
    public function &setScale($value)
    {
        $this->scale = (int) $value;

        return $this;
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
        return 'float';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return '(float) $value';
    }
}
