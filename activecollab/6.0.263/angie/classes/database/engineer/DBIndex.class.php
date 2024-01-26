<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Class that represents table index in a database.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBIndex
{
    /**
     * Index types.
     */
    const PRIMARY = 0;
    const UNIQUE = 1;
    const KEY = 2;
    const FULLTEXT = 3;

    /**
     * Index name.
     *
     * @var string
     */
    protected $name;

    /**
     * Array of columns for composite keys.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Key type.
     *
     * @var int
     */
    protected $type = self::KEY;

    /**
     * Parent DB table.
     *
     * @var DBTable
     */
    protected $table;

    /**
     * Construct DBIndex.
     *
     * If $columns is NULL, system will create index for the field that has the
     * same name as the index
     *
     * @param string $name
     * @param int    $type
     * @param mixed  $columns
     */
    public function __construct($name, $type = self::KEY, $columns = null)
    {
        $this->name = $name;
        $this->type = $type;

        if ($name == 'PRIMARY') {
            $this->type = self::PRIMARY;
        }

        // Use column name
        if ($columns === null) {
            $this->addColumn($name);

            // Columns are specified
        } elseif ($columns) {
            $columns = is_array($columns) ? $columns : [$columns];
            foreach ($columns as $column) {
                if ($column instanceof DBColumn) {
                    $this->addColumn($column->getName());
                } else {
                    $this->addColumn($column);
                }
            }
        }
    }

    /**
     * Add a column to the list of columns.
     *
     * @param string $column_name
     */
    public function addColumn($column_name)
    {
        $this->columns[] = $column_name;
    }

    /**
     * Create and return new index instance.
     *
     * @param  string  $name
     * @param  int     $type
     * @param  mixed   $columns
     * @return DBIndex
     */
    public static function create($name, $type = self::KEY, $columns = null)
    {
        return new self($name, $type, $columns);
    }

    /**
     * Load index data from row returned by SHOW INDEX query.
     *
     * @param array $row
     */
    public function loadFromRow($row)
    {
        $this->columns[] = $row['Column_name'];

        if ($this->name == 'PRIMARY') {
            $this->type = self::PRIMARY;
        } elseif ($row['Index_type'] == 'FULLTEXT') {
            $this->type = self::FULLTEXT;
        } elseif (!(bool) $row['Non_unique']) {
            $this->type = self::UNIQUE;
        } else {
            $this->type = self::KEY;
        }
    }

    /**
     * Interface to columns array.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Prepare key definition.
     *
     * @return string
     */
    public function prepareDefinition()
    {
        switch ($this->type) {
            case self::PRIMARY:
                $result = 'PRIMARY KEY';
                break;
            case self::UNIQUE:
                $result = 'UNIQUE ' . DB::escapeFieldName($this->name);
                break;
            case self::FULLTEXT:
                $result = 'FULLTEXT ' . DB::escapeFieldName($this->name);
                break;
            default:
                $result = 'INDEX ' . DB::escapeFieldName($this->name);
                break;
        }

        $column_names = [];
        foreach ($this->columns as $column) {
            $column_names[] = DB::escapeFieldName($column);
        }

        return $result . ' (' . implode(', ', $column_names) . ')';
    }

    /**
     * Return model definition code for this index.
     *
     * @return string
     */
    public function prepareModelDefinition()
    {
        if (count($this->columns) == 1) {
            $columns = var_export($this->columns[0], true);
        } else {
            $columns = [];
            foreach ($this->columns as $k => $v) {
                $columns[] = var_export($v, true);
            }
            $columns = 'array(' . implode(', ', $columns) . ')';
        }

        // Primary key
        if ($this->type == self::PRIMARY) {
            return "DBIndexPrimary::create($columns)";

            // Index where the name of the index is the same as the column
        } elseif ($this->type == self::KEY && count($this->columns) == 1 && $this->getName() == $this->columns[0]) {
            return "DBIndex::create('" . $this->getName() . "')";

            // Everything else
        } else {
            switch ($this->type) {
                case self::UNIQUE:
                    $type = 'DBIndex::UNIQUE';
                    break;
                case self::FULLTEXT:
                    $type = 'DBIndex::FULLTEXT';
                    break;
                default:
                    $type = 'DBIndex::KEY';
                    break;
            }

            return "DBIndex::create('" . $this->getName() . "', $type, $columns)";
        }
    }

    // ---------------------------------------------------
    //  Type
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
     * @param  string  $value
     * @return DBIndex
     */
    public function &setName($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Returns true if this key is primary key.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return $this->type == self::PRIMARY;
    }

    // ---------------------------------------------------
    //  Getters and setters
    // ---------------------------------------------------

    /**
     * Returns true if this is UNIQUE key.
     *
     * @return bool
     */
    public function isUnique()
    {
        return ($this->type == self::PRIMARY) || ($this->type == self::UNIQUE);
    }

    /**
     * Returns true if this is FULLTEXT key.
     *
     * @return bool
     */
    public function isFulltext()
    {
        return $this->type == self::FULLTEXT;
    }

    /**
     * Return type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param  int     $value
     * @return DBIndex
     */
    public function &settype($value)
    {
        $this->type = $value;

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
     * @param  DBTable $value
     * @return DBIndex
     */
    public function &setTable(DBTable $value)
    {
        $this->table = $value;

        return $this;
    }
}
