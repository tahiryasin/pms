<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Categories context implementation.
 *
 * @package angie.frameworks.categories
 * @subpackage models
 */
trait ICategoriesContextImplementation
{
    /**
     * Return categories, optionally filtered by type.
     *
     * @param  string     $type
     * @return Category[]
     */
    public function getCategories($type = null)
    {
        return Categories::findByParams($this, $type);
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return ID of this instance.
     *
     * @return int
     */
    abstract public function getId();
}
