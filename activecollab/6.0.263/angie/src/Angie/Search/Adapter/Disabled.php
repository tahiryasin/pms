<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Search\Adapter;

use Angie\Search\SearchItem\SearchItemInterface;
use Angie\Search\SearchResult\SearchResult;
use User;

/**
 * Disabled search adapter (black hole).
 *
 * @package Angie\Search\Adapter
 */
final class Disabled extends Adapter
{
    /**
     * {@inheritdoc}
     */
    public function indexStatus()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex($force = true)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDocuments()
    {
    }

    /**
     * Return indexed record.
     *
     * @param  SearchItemInterface $item
     * @return array|null
     * @todo
     */
    public function get(SearchItemInterface $item)
    {
    }

    /**
     * Add an item to the index.
     *
     * @param SearchItemInterface $item
     * @param bool                $bulk
     */
    public function add(SearchItemInterface $item, $bulk = false)
    {
    }

    /**
     * Update an item.
     *
     * @param SearchItemInterface $item
     * @param bool                $bulk
     */
    public function update(SearchItemInterface $item, $bulk = false)
    {
    }

    /**
     * Remove an item.
     *
     * @param SearchItemInterface $item
     * @param bool                $bulk
     */
    public function remove(SearchItemInterface $item, $bulk = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function query($search_for, User $user, $criterions = null, $page = 1, $documents_per_page = 25)
    {
        return new SearchResult([], 1, 25, 0, 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function isReady()
    {
        return true;
    }
}
