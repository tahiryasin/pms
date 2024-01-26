<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Subtask activity log trait.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
trait SubtaskActivityLog
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), ['subtask_id' => $this->getSubtaskId()]);
    }

    /**
     * @param Subtask $subtask
     */
    public function setSubtask(Subtask $subtask)
    {
        $this->setAdditionalProperty('subtask_id', $subtask->getId());
    }

    /**
     * This method is called when we need to load related notification objects for API response.
     *
     * @param array $type_ids_map
     */
    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
        parent::onRelatedObjectsTypeIdsMap($type_ids_map);

        if ($subtask = $this->getSubtask()) {
            if (empty($type_ids_map[Subtask::class])) {
                $type_ids_map[Subtask::class] = [];
            }

            if (!in_array($subtask->getId(), $type_ids_map[Subtask::class])) {
                $type_ids_map[Subtask::class][] = $subtask->getId();
            }
        }
    }

    /**
     * @return int
     */
    public function getSubtaskId()
    {
        return (int) $this->getAdditionalProperty('subtask_id');
    }

    /**
     * Return subtask instance.
     *
     * @return Subtask
     */
    public function getSubtask()
    {
        return DataObjectPool::get(Subtask::class, $this->getSubtaskId());
    }
}
