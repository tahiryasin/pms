<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Foudation class that describes general database column properties.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
abstract class DBColumn
{
    /**
     * Size variations.
     *
     * @var string
     */
    const TINY = 'tiny';
    const SMALL = 'small';
    const NORMAL = 'normal';
    const MEDIUM = 'medium';
    const BIG = 'big';

    /**
     * Column name.
     *
     * @var string
     */
    protected $name;

    /**
     * Default value.
     *
     * @var mixed
     */
    protected $default = null;

    /**
     * Field comment.
     *
     * @var string
     */
    protected $comment = null;

    /**
     * Field size, if field has it.
     *
     * @var string
     */
    protected $size = self::NORMAL;

    /**
     * True for fields that have size (TINY, SMALL, NORMAL, MEDIUM, BIG).
     *
     * @var bool
     */
    protected $has_size = false;

    /**
     * Indicates whether this column can have default value or not.
     *
     * @var bool
     */
    protected $has_default = true;

    /**
     * Parent table.
     *
     * @var DBTable
     */
    protected $table;

    /**
     * Construct database column.
     *
     * @param string $name
     * @param mixed  $default
     */
    public function __construct($name, $default = null)
    {
        $this->name = $name;
        $this->default = $default;
    }

    /**
     * Load column information from row returned from SHOW COLUMNS query.
     *
     * @param array $row
     */
    public function loadFromRow($row)
    {
        $this->default = $row['Null'] == 'NO' && $row['Default'] !== null ? $row['Default'] : null;
    }

    /**
     * Process additional parameters like VARCHAR(LENGHT), INT(10) or FLOAT(4,2).
     *
     * @param array $additional
     */
    public function processAdditional($additional)
    {
    }

    /**
     * Prepare field definition.
     *
     * @return string
     */
    public function prepareDefinition()
    {
        $result = DB::escapeFieldName($this->name) . ' ' . $this->prepareTypeDefinition() . ' ' . $this->prepareNull();

        if ($this->has_default && $this->prepareDefault() !== '') {
            $result .= ' DEFAULT ' . $this->prepareDefault();
        }

        if ($this->comment) {
            $result .= ' COMMENT ' . DB::escape($this->comment);
        }

        return $result;
    }

    /**
     * Prepare type definition.
     *
     * @return string
     */
    abstract public function prepareTypeDefinition();

    /**
     * Prepare null / not null part of the definition.
     *
     * @return string
     */
    protected function prepareNull()
    {
        return $this->default === null ? '' : 'NOT NULL';
    }

    /**
     * Prepare default value.
     *
     * @return string
     */
    public function prepareDefault()
    {
        if ($this->default === null) {
            return 'NULL';
        } elseif ($this->default === 0) {
            return '0';
        } elseif ($this->default === '') {
            return "''";
        } else {
            return DB::escape($this->default);
        }
    }

    /**
     * Return model definition code for this column.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        $default = $this->getDefault() === null ? '' : ', ' . var_export($this->getDefault(), true);

        $result = get_class($this) . "::create('" . $this->getName() . "'$default)";

        if ($this->has_size && $this->getSize() != self::NORMAL) {
            switch ($this->getSize()) {
                case self::TINY:
                    $result .= '->setSize(DBColumn::TINY)';
                    break;
                case self::SMALL:
                    $result .= '->setSize(DBColumn::SMALL)';
                    break;
                case self::MEDIUM:
                    $result .= '->setSize(DBColumn::MEDIUM)';
                    break;
                case self::BIG:
                    $result .= '->setSize(DBColumn::BIG)';
                    break;
            }
        }

        return $result;
    }

    /**
     * Return default.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set default.
     *
     * @param  mixed    $value
     * @return DBColumn
     */
    public function &setDefault($value)
    {
        $this->default = $value;

        return $this;
    }

    // ---------------------------------------------------
    //  Model generator
    // ---------------------------------------------------

    /**
     * Return name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param  string   $value
     * @return DBColumn
     */
    public function &setName($value)
    {
        $this->name = $value;

        return $this;
    }

    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------

    /**
     * Return size.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set size.
     *
     * @param  string   $value
     * @return DBColumn
     */
    public function &setSize($value)
    {
        $this->size = $value;

        return $this;
    }

    /**
     * Check if this column belogs to an index.
     *
     * @return bool
     */
    public function isPrimaryKey()
    {
        foreach ($this->table->getIndices() as $index) {
            if (in_array($this->name, $index->getColumns()) && $index->isPrimary()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
    }

    /**
     * Return verbose PHP type.
     *
     * @return string
     */
    public function getPhpType()
    {
        return 'string';
    }

    /**
     * Return PHP bit that will cast raw value to proper value.
     *
     * @return string
     */
    public function getCastingCode()
    {
        return '(string) $value';
    }

    /**
     * Return comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set comment.
     *
     * @param  string   $value
     * @return DBColumn
     */
    public function &setComment($value)
    {
        $this->comment = $value;

        return $this;
    }

    /**
     * Return table.
     *
     * @return DBTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set table.
     *
     * @param  DBTable  $value
     * @return DBColumn
     */
    public function &setTable(DBTable $value)
    {
        $this->table = $value;

        return $this;
    }
}
