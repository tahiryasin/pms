<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Search\SearchItem;

use ActiveCollab\Foundation\Urls\PermalinkInterface;
use Angie\Search\SearchDocument\SearchDocumentInterface;

/**
 * Search item interface.
 *
 * @package Angie\Search
 */
interface SearchItemInterface extends PermalinkInterface
{
    const FIELD_BOOLEAN = 'boolean';
    const FIELD_NUMERIC = 'numeric';
    const FIELD_DATE = 'date';
    const FIELD_DATETIME = 'datetime';
    const FIELD_STRING = 'string';
    const FIELD_TEXT = 'text';

    /**
     * Return a list of fields that are indexed for this type.
     *
     * @return string[]
     */
    public function getSearchFields();

    /**
     * Track changes for the given field(s).
     *
     * @param string[] ...$field_names
     */
    public function addSearchFields(...$field_names);

    public function getSearchDocument(): SearchDocumentInterface;

    /**
     * Return type under which it this object is stored in the search index.
     *
     * @return string
     */
    public function getSearchIndexType();

    /**
     * Get object ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @param  bool   $singular
     * @return string
     */
    public function getModelName($underscore = false, $singular = false);
}
