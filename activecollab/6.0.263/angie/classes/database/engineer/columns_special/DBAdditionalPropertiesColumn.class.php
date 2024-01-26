<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Additional properties column implementation.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBAdditionalPropertiesColumn extends DBTextColumn
{
    /**
     * Construct additional properties column.
     */
    public function __construct()
    {
        parent::__construct('raw_additional_properties');
        $this->setSize(self::BIG);
    }

    /**
     * Executed when this field is added to a table.
     */
    public function addedToTable()
    {
        $this->table->addModelTrait('IAdditionalProperties', 'IAdditionalPropertiesImplementation');

        parent::addedToTable();
    }
}
