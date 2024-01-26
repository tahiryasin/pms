<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents TEXT database columns.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBTextColumn extends DBColumn
{
    /**
     * Text fields have size.
     *
     * @var bool
     */
    protected $has_size = true;

    /**
     * Text columns can't have default value.
     *
     * @var bool
     */
    protected $has_default = false;

    /**
     * Construct without default value.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

    /**
     * Create and return tme column.
     *
     * @param  string       $name
     * @return DBTextColumn
     */
    public static function create($name)
    {
        return new self($name);
    }

    /**
     * Return type definition string.
     *
     * @return string
     */
    public function prepareTypeDefinition()
    {
        switch ($this->size) {
            case self::BIG:
                return 'longtext';
            case self::SMALL:
            case self::NORMAL:
                return 'text';
            default:
                return $this->size . 'text';
        }
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        if ($this->name == 'raw_additional_properties') {
            return 'DBAdditionalPropertiesColumn::create()';
        } else {
            $result = "DBTextColumn::create('" . $this->getName() . "')";

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
    }
}
