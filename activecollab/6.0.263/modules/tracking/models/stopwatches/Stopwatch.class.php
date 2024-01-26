<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Stopwatch class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
final class Stopwatch extends BaseStopwatch
{
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'parent_type' => $this->getParentType(),
            'parent_id' => $this->getParentId(),
            'user_id' => $this->getUserId(),
            'user_name' => $this->getUserName(),
            'user_email' => $this->getUserEmail(),
            'started_on' => $this->getStartedOn(),
            'is_kept' => $this->getIsKept(),
            'elapsed' => $this->getElapsed(),
            'updated_on' => $this->getUpdatedOn(),
            'created_on' => $this->getCreatedOn(),
        ];
    }

    public function whoCanSeeThis()
    {
        return [$this->getUserId()];
    }
}
