<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Categories context interface.
 *
 * @package angie.frameworks.categories
 * @subpackage models
 */
interface ICategoriesContext
{
    /**
     * Return categories, optionally filtered by type.
     *
     * @param  string     $type
     * @return Category[]
     */
    public function getCategories($type = null);

    /**
     * @return int
     */
    public function getId();
}
