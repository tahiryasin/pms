<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Name column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBNameColumn extends DBStringColumn
{
    /**
     * Flag whether this name field should be kept unique.
     *
     * @var bool
     */
    private $unique = false;

    /**
     * Additional fields that are used to validate uniqueness of the name.
     *
     * @var array
     */
    private $unique_context = null;

    /**
     * Construct name column instance.
     *
     * @param int|string $length
     * @param bool       $unique
     * @param array      $unique_context
     */
    public function __construct($length = self::MAX_LENGTH, $unique = false, $unique_context = null)
    {
        parent::__construct('name', $length, '');

        $this->unique = (bool) $unique;

        if ($unique_context) {
            $this->unique_context = (array) $unique_context;
        }
    }

    /**
     * Create and return instance of name column.
     *
     * @param  int|string   $length
     * @param  bool         $unique
     * @param  string       $unique_context
     * @return DBNameColumn
     */
    public static function create($length = self::MAX_LENGTH, $unique = false, $unique_context = null)
    {
        return new self($length, $unique, $unique_context);
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->unique) {
            $context = ['name'];

            if (is_array($this->unique_context)) {
                $context = array_merge($context, $this->unique_context);
            }

            $this->table->addIndex(new DBIndex('name', DBIndex::UNIQUE, $context));
        }

        parent::addedToTable();
    }
}
