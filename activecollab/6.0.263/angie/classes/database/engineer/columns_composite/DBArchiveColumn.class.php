<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Archive column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBArchiveColumn extends DBCompositeColumn
{
    private $cascade;
    private $record_archive_timestamp;

    /**
     * Construct archive column instance.
     *
     * Set $cascade to true in cases where model can be archived when parent object is archived.
     *
     * @param bool $cascade
     * @param bool $record_archive_timestamp
     */
    public function __construct($cascade = false, $record_archive_timestamp = false)
    {
        $this->cascade = $cascade;
        $this->record_archive_timestamp = $record_archive_timestamp;

        $this->columns = [
            DBBoolColumn::create('is_archived'),
        ];

        if ($this->cascade) {
            $this->columns[] = DBBoolColumn::create('original_is_archived');
        }

        if ($this->record_archive_timestamp) {
            $this->columns[] = DBDateTimeColumn::create('archived_on');
        }
    }

    /**
     * Event that table triggers when this column is added to the table.
     */
    public function addedToTable()
    {
        if ($this->record_archive_timestamp) {
            $this->table->addIndex(new DBIndex('archived_on'));
        }

        parent::addedToTable();
    }
}
