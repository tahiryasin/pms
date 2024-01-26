<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * File meta data composite column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBFileMetaColumn extends DBCompositeColumn
{
    /**
     * Construct visibility column instance.
     */
    public function __construct()
    {
        $this->columns = [
            DBNameColumn::create(150),
            DBStringColumn::create('mime_type', DBStringColumn::MAX_LENGTH, 'application/octet-stream'),
            DBIntegerColumn::create('size', 10, 0)->setUnsigned(true),
            DBStringColumn::create('location', DBStringColumn::MAX_LENGTH),
            DBStringColumn::create('md5', 32),
        ];
    }

    /**
     * Construct and return visibility column.
     *
     * @return DBFileMetaColumn
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Trigger after this column gets added to the table.
     */
    public function addedToTable()
    {
        $this->table->addModelTrait('IFile', 'IFileImplementation');
        $this->table->addIndex(DBIndex::create('location'));

        parent::addedToTable();
    }
}
