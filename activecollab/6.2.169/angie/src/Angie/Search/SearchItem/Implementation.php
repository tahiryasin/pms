<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Search\SearchItem;

use Angie\Search\SearchEngineInterface;
use IBody;
use ICreatedBy;
use ICreatedOn;
use InvalidParamError;
use ITrash;

/**
 * Base search item implementation.
 *
 * @package Angie\Search
 */
trait Implementation
{
    /**
     * @var bool
     */
    private $update_search_on_next_save = true;

    /**
     * Say hello to the parent object.
     */
    public function AngieSearchSearchItemImplementation()
    {
        $this->registerEventHandler('on_after_save', function ($is_new, $modifications) {
            if ($this->update_search_on_next_save) {
                if ($is_new) {
                    $this->getSearchEngine()->add($this);
                } elseif (!empty($modifications) && $this->shouldSearchIndexBeUpdated($modifications)) {
                    $this->getSearchEngine()->update($this);
                }
            } else {
                $this->update_search_on_next_save = true;
            }
        });

        $this->registerEventHandler('on_after_delete', function ($bulk) {
            $this->getSearchEngine()->remove($this, $bulk);
        });

        if ($this instanceof ITrash) {
            $this->registerEventHandler('on_after_move_to_trash', function ($bulk) {
                $this->getSearchEngine()->remove($this, $bulk);
            });

            $this->registerEventHandler('on_after_restore_from_trash', function ($bulk) {
                $this->getSearchEngine()->add($this, $bulk);
            });
        }

        $search_fields = [];

        foreach (['type', 'name'] as $common_field) {
            if ($this->fieldExists($common_field)) {
                $search_fields[] = $common_field;
            }
        }

        if ($this instanceof ICreatedOn) {
            $search_fields[] = 'created_on';
        }

        if ($this instanceof ICreatedBy) {
            $search_fields[] = 'created_by_id';
        }

        if ($this instanceof IBody) {
            $search_fields[] = 'body';
        }

        $this->addSearchFields(...$search_fields);
    }

    /**
     * Return true if we should update the index.
     *
     * @param  array $modifications
     * @return bool
     */
    private function shouldSearchIndexBeUpdated($modifications)
    {
        if ($this instanceof ITrash && !empty($modifications['is_trashed'])) {
            return false; // Let the trash events handle the index refresh
        }

        return count(array_intersect($this->getSearchFields(), array_keys($modifications))) > 0;
    }

    private $search_fields = [];

    /**
     * {@inheritdoc}
     */
    public function getSearchFields()
    {
        return $this->search_fields;
    }

    /**
     * {@inheritdoc}
     */
    public function addSearchFields(...$field_names)
    {
        foreach ($field_names as $field_name) {
            if (!in_array($field_name, $this->search_fields)) {
                $this->search_fields[] = $field_name;
            }
        }
    }

    /**
     * Serialize object to be indexed.
     *
     * @return array
     */
    public function searchSerialize()
    {
        $result = [
            'id' => $this->getId(),
            'type' => $this->getModelName(false, true),
            'name' => $this->getName(),
            'url' => $this->getUrlPath(),
        ];

        $this->triggerEvent('on_search_serialize', [&$result]);

        return $result;
    }

    /**
     * Return type under which it this object is stored in the search index.
     *
     * @return string
     */
    public function getSearchIndexType()
    {
        return $this->getModelName(false, true);
    }

    /**
     * Call this method if you do not want to update the search record on the next save call.
     *
     * This is useful if you are creating an object (invoice) with related objects that are going to be added after
     * $this is saved and are relevant for the serach (invoice items)
     */
    public function dontUpdateSearchIndexOnNextSave()
    {
        $this->update_search_on_next_save = false;
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * @return SearchEngineInterface
     */
    abstract protected function getSearchEngine();

    /**
     * Return object ID.
     *
     * @return int
     */
    abstract public function getId();

    /**
     * Return name.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @param  bool   $singular
     * @return string
     */
    abstract public function getModelName($underscore = false, $singular = false);

    /**
     * Return object path.
     *
     * @return string
     */
    abstract public function getObjectPath();

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Trigger an internal event.
     *
     * @param string $event
     * @param array  $event_parameters
     */
    abstract protected function triggerEvent($event, $event_parameters = null);

    abstract public function getUrlPath(): string;
}
