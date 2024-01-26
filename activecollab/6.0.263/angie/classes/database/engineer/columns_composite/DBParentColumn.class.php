<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

/**
 * Parent composite column.
 *
 * @package angie.library.database
 * @subpackage engineer
 */
class DBParentColumn extends DBRelatedObjectColumn
{
    /**
     * Construct parent column instance.
     *
     * @param bool $add_key
     * @param bool $can_be_null
     */
    public function __construct($add_key = true, $can_be_null = true)
    {
        parent::__construct('parent', $add_key, $can_be_null);
    }

    /**
     * Execute when this field gets added to table.
     */
    public function addedToTable()
    {
        $this->table->addModelTrait(
            [
                RoutingContextInterface::class => null,
                IChild::class => IChildImplementation::class,
            ]
        );

        parent::addedToTable();
    }
}
