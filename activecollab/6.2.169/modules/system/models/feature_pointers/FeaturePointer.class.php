<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * FeaturePointer class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class FeaturePointer extends BaseFeaturePointer
{
    public function shouldShow(User $user): bool
    {
        return empty(
            DB::executeFirstCell(
                'SELECT fp.id
                    FROM feature_pointer_dismissals fpd
                     LEFT JOIN feature_pointers fp ON fp.id = fpd.feature_pointer_id
                      WHERE fpd.user_id = ? AND fp.type = ?',
                $user->getId(),
                get_class($this)
            )
        );
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'parent_id' => $this->getParentId(),
            'description' => $this->getDescription(),
            'created_on' => $this->getCreatedOn(),
        ];
    }
}
