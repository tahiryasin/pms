<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents BLOB/BINARY database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBBinaryColumn extends DBColumn
{
    /**
     * Binary fields have size.
     *
     * @var bool
     */
    protected $has_size = true;

    /**
     * Binary columns can't have default value.
     *
     * @var bool
     */
    protected $has_default = false;

    /**
     * {@inheritdoc}
     */
    public static function create($name, $default = null)
    {
        return new self($name);
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        $result = "DBBinaryColumn::create('" . $this->getName() . "')";

        if ($this->getSize() != DBColumn::NORMAL) {
            switch ($this->getSize()) {
                case DBColumn::TINY:
                    $result .= '->setSize(DBColumn::TINY)';
                    break;
                case DBColumn::SMALL:
                    $result .= '->setSize(DBColumn::SMALL)';
                    break;
                case DBColumn::MEDIUM:
                    $result .= '->setSize(DBColumn::MEDIUM)';
                    break;
                case DBColumn::BIG:
                    $result .= '->setSize(DBColumn::BIG)';
                    break;
            }
        }

        return $result;
    }

    /**
     * Prepare type definition.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        switch ($this->size) {
            case self::BIG:
                return 'longblob';
            case self::SMALL:
            case self::NORMAL:
                return 'blob';
            default:
                return $this->size . 'blob';
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
