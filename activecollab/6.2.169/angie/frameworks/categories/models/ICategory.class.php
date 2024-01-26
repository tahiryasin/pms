<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Category interface.
 *
 * @package angie.frameworks.categories
 * @subpackage models
 */
interface ICategory
{
    /**
     * Return parent's category.
     *
     * @return Category|null
     */
    public function getCategory();

    /**
     * Return parent's category.
     *
     * @param  Category|null $category
     * @param  bool          $save
     * @return Category|null
     */
    public function setCategory($category, $save = false);

    /**
     * Return category context (global by default).
     *
     * @return ICategoriesContext|null
     */
    public function getCategoryContext();

    /**
     * Return category context string.
     *
     * @return string
     */
    public function getCategoryContextString();

    /**
     * Return category ID.
     *
     * @return int
     */
    public function getCategoryId();

    /**
     * Set category ID.
     *
     * @param  int $value
     * @return int
     */
    public function setCategoryId($value);
}
