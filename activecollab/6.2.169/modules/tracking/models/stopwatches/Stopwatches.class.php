<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\Tracking\Services\StopwatchServiceInterface;

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
            return (new StopwatchesCollection($collection_name))
                ->setWhosAsking($user);
        }

        return $collection;
    }

    public static function deleteByTask(Task $task)
    {
        $stopwatches = self::find([
            'conditions' => ['parent_type = ? AND parent_id = ?', Task::class, $task->getId()],
            'one' => false,
        ]);

        if ($stopwatches) {
            foreach ($stopwatches as $stopwatch) {
                return AngieApplication::getContainer()
                    ->get(StopwatchServiceInterface::class)
                    ->delete($stopwatch);
            }
        }
    }
}
