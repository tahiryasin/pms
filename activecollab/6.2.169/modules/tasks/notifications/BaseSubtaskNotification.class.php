<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Base subtask notification.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage notifications
 */
abstract class BaseSubtaskNotification extends Notification
{
    /**
     * Serialize to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), ['subtask_id' => $this->getSubtaskId()]);
    }

    /**
     * Return subtask ID.
     *
     * @return int
     */
    public function getSubtaskId()
    {
        return $this->getAdditionalProperty('subtask_id');
    }

    /**
     * Set subtask.
     *
     * @param  Subtask                 $subtask
     * @return BaseSubtaskNotification
     */
    public function &setSubtask(Subtask $subtask)
    {
        $this->setAdditionalProperty('subtask_id', $subtask->getId());

        return $this;
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        return ['subtask' => $this->getSubtask(), 'project' => $this->getParent()->getProject()];
    }

    /**
     * Return subtask instance.
     *
     * @return Subtask
     */
    public function getSubtask()
    {
        return DataObjectPool::get('Subtask', $this->getSubtaskId());
    }

    /**
     * This method is called when we need to load related notification objects for API response.
     *
     * @param array $type_ids_map
     */
    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
        if (empty($type_ids_map['Subtask'])) {
            $type_ids_map['Subtask'] = [];
        }

        if (!in_array($this->getSubtaskId(), $type_ids_map['Subtask'])) {
            $type_ids_map['Subtask'][] = $this->getSubtaskId();
        }
    }
}
