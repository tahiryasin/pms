<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Abstract initial settings collection.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
abstract class AbstractInitialSettingsCollection extends CompositeCollection
{
    use IWhosAsking;

    /**
     * Return model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return 'Users';
    }

    /**
     * @return array
     */
    public function execute()
    {
        $result = ['settings' => $this->getSettings()];

        foreach ($this->getCollections() as $collection_name => $collection) {
            if ($collection->getCurrentPage() && $collection->getItemsPerPage()) {
                $result["{$collection_name}_total"] = $collection->count();
            }

            $result[$collection_name] = $collection->execute();

            if (empty($result[$collection_name])) {
                $result[$collection_name] = [];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    abstract protected function getSettings();

    /**
     * @return ModelCollection[]
     */
    abstract protected function &getCollections();

    /**
     * Return number of records that match conditions set by the collection.
     *
     * @return int
     */
    public function count()
    {
        $result = count($this->getSettings());

        foreach ($this->getCollections() as $collection) {
            $result += $collection->count();
        }

        return $result;
    }

    /**
     * @param array $settings
     * @param User  $user
     */
    protected function onLoadSettings(array &$settings, User $user)
    {
    }

    /**
     * @param array $collections
     * @param User  $user
     */
    protected function onLoadCollections(array &$collections, User $user)
    {
    }
}
