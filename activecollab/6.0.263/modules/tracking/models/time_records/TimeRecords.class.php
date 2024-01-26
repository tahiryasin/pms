<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Time records manager class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class TimeRecords extends BaseTimeRecords
{
    use ITrackingObjectsImplementation;

    /**
     * Return new collection.
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'time_records_in_project') || str_starts_with($collection_name, 'filtered_time_records_in_project')) {
            return (new ProjectTimeRecordsCollection($collection_name))->setWhosAsking($user);
        } else {
            if (str_starts_with($collection_name, 'time_records_in_task')) {
                return (new TaskTimeRecordsCollection($collection_name))->setWhosAsking($user);
            } else {
                if (str_starts_with($collection_name, 'time_records_by_user') || str_starts_with($collection_name, 'filtered_time_records_by_user')) {
                    return (new UserTimeRecordsCollection($collection_name))->setWhosAsking($user);
                } else {
                    throw new InvalidParamError('collection_name', $collection_name);
                }
            }
        }
    }

    /**
     * Return time records by parent.
     *
     * @param  ITracking $parent
     * @param  int       $billable_status
     * @return DBResult
     */
    public static function findByParent(ITracking $parent, $billable_status = null)
    {
        if ($billable_status) {
            return self::find([
                'conditions' => ['parent_type = ? AND parent_id = ? AND billable_status = ? AND is_trashed = ?', get_class($parent), $parent->getId(), $billable_status, false],
            ]);
        } else {
            return self::find([
                'conditions' => ['parent_type = ? AND parent_id = ? AND is_trashed = ?', get_class($parent), $parent->getId(), false],
            ]);
        }
    }

    /**
     * Sum time by task.
     *
     * @param  Task  $task
     * @return float
     */
    public static function sumByTask(Task $task)
    {
        $time_value = 0;

        if ($time_records = DB::execute('SELECT value FROM time_records WHERE ' . self::parentToCondition($task) . ' AND is_trashed = ?', false)) {
            foreach ($time_records as $time_record) {
                $time_value += time_to_minutes($time_record['value']);
            }
        }

        return minutes_to_time($time_value);
    }

    /**
     * Find time records by task list.
     *
     * @param  TaskList  $task_list
     * @param  int|int[] $statuses
     * @return array
     */
    public static function findByTaskList(TaskList $task_list, $statuses)
    {
        if ($task_ids = DB::executeFirstColumn('SELECT id FROM tasks WHERE task_list_id = ? AND project_id = ? AND is_trashed = ?', $task_list->getId(), $task_list->getProjectId(), false)) {
            return self::find([
                'conditions' => ['parent_type = ? AND parent_id IN (?) AND billable_status IN (?) AND is_trashed = ?', 'Task', $task_ids, $statuses, false],
            ]);
        }

        return null;
    }

    /**
     * Group records by job type.
     *
     * @param  TimeRecord[] $records
     * @return array
     */
    public static function groupByJobType($records)
    {
        $grouped = [];

        if (is_foreachable($records)) {
            foreach ($records as $time_record) {
                $key = $time_record->getJobTypeName();

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }

                $grouped[$key][] = $time_record;
            }
        }

        return $grouped;
    }

    /**
     * Check if all time records have the same job hourly rate.
     *
     * @param  TimeRecord[] $records
     * @return mixed        unit_cost or false
     */
    public static function isIdenticalJobRate($records)
    {
        if (is_foreachable($records)) {
            $previous = null;

            foreach ($records as $time_record) {
                $job_type_id = $time_record->getJobTypeId();
                $project = $time_record->getProject();

                $job_type_rates = JobTypes::getIdRateMapFor($project); //job_type_id => cost

                $job_type_rate = isset($job_type_rates[$job_type_id]) ? $job_type_rates[$job_type_id] : 0;

                if ($previous !== null && $job_type_rate != $previous) {
                    return false;
                }

                $previous = $job_type_rate;
            }

            return $previous;
        }

        return true;
    }

    /**
     * Return number of time records that use this particular job type.
     *
     * @param  JobType $job_type
     * @return int
     */
    public static function countByJobType(JobType $job_type)
    {
        return self::count(['job_type_id = ?', $job_type->getId()]);
    }

    /**
     * Change billable status by IDs.
     *
     * @param  int[]    $ids
     * @param  int      $new_status
     * @return DbResult
     */
    public static function changeBilableStatusByIds($ids, $new_status)
    {
        return DB::execute('UPDATE time_records SET billable_status = ? WHERE id IN (?)', $new_status, $ids);
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $time_record = parent::create($attributes, $save, false);

        return DataObjectPool::announce($time_record, DataObjectPool::OBJECT_CREATED, $attributes);
    }

    public static function canAccessUsersTimeRecords(User $whos_asking, User $for_user)
    {
        if (!$for_user->isLoaded()) {
            return false;
        }

        if ($for_user->isClient()) {
            return false;
        }

        return $whos_asking->is($for_user) || $whos_asking->isOwner();
    }

    public static function preloadDetailsByIds(array $time_records_ids)
    {
        DataObjectPool::getByIds(TimeRecord::class, $time_records_ids);
    }
}
