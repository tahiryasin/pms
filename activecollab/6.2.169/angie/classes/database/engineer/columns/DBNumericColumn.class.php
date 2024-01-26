<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Numeric columns class (as foundation for integers and floats).
 *
 * @package angie.library.database
 * @subpackage subpackage
 */
abstract class DBNumericColumn extends DBColumn
{
    /**
     * Integer fields have size.
     *
     * @var bool
     */
    protected $has_size = true;

    /**
     * Field length.
     *
     * @var int
     */
    protected $length;

    /**
     * Check if this column is unsisgned or not.
     *
     * @var bool
     */
    protected $unsigned = false;

    /**
     * Construct numeric column.
     *
     * @param string     $name
     * @param string|int $lenght
     * @param mixed      $default
     */
    public function __construct($name, $lenght = DBColumn::NORMAL, $default = null)
    {
        parent::__construct($name, $default);

        $this->length = (int) $lenght;
    }

    /**
     * Load numberic field details from row.
     *
     * @param array $row
     */
    public function loadFromRow($row)
    {
        parent::loadFromRow($row);
        $this->unsigned = strpos($row['Type'], 'unsigned') !== false;
    }

    /**
     * Process additional parameters.
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
     * Set field lenght.
     *
     * @param  int             $value
     * @return DBNumericColumn
     */
    public function &setLenght($value)
    {
        $this->length = (int) $value;

        return $this;
    }

    /**
     * Return unsigned.
     *
     * @return bool
     */
    public function getUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * Set unsigned column flag.
     *
     * @param  bool            $value
     * @return DBNumericColumn
     */
    public function &setUnsigned($value)
    {
        $this->unsigned = (bool) $value;

        return $this;
    }
}
