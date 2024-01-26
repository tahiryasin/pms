<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * New note notification.
 *
 * @package ActiveCollab.modules.notes
 * @subpackage notifications
 */
class NewNoteNotification extends Notification
{
    use INewInstanceUpdate, INewProjectElementNotificationOptOutConfig;

    /**
     * This method is called when we need to load related notification objects for API response.
     *
     * @param array $type_ids_map
     */
    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
        $note = $this->getParent();

        if ($note instanceof Note && (empty($type_ids_map['Project']) || !in_array($note->getProjectId(), $type_ids_map['Project']))) {
            $type_ids_map['Project'][] = $note->getProjectId();
        }
    }
}
