<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Stopwatches class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class Stopwatches extends BaseStopwatches
{
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);
        if (str_starts_with($collection_name, 'user_stopwatches')) {
            $collection->setConditions('user_id = ?', $user->getId());

            return $collection;
        }

        return $collection;
    }
}
