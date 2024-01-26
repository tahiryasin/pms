<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Expenses class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class Expenses extends BaseExpenses
{
    use ITrackingObjectsImplementation;

    /**
     * Return new collection.
     *
     * @param  string             $collection_name
     * @param  User|null          $user
     * @return ExpensesCollection
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'expenses_in_project')) {
            return (new ProjectExpensesCollection($collection_name))->setWhosAsking($user);
        } else {
            if (str_starts_with($collection_name, 'expenses_in_task')) {
                return (new TaskExpensesCollection($collection_name))->setWhosAsking($user);
            } else {
                throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
            }
        }
    }

    /**
     * Return expenses by given category.
     *
     * @param expenseCategory
     * @return array
     */
    public static function findByCategory(ExpenseCategory $category)
    {
        return self::find(['conditions' => ['category_id = ? AND is_trashed = ?', $category->getId(), false]]);
    }

    /**
     * Return number of expenses by category.
     *
     * @param  ExpenseCategory $category
     * @return int
     */
    public static function countByCategory(ExpenseCategory $category)
    {
        return self::count(['category_id = ?', $category->getId()]);
    }

    /**
     * Return expenses by parent.
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
        return (float) DB::executeFirstCell('SELECT SUM(value) FROM expenses WHERE ' . self::parentToCondition($task) . ' AND is_trashed = ?', false);
    }

    /**
     * Find expenses by task list.
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
     * Change billable status by IDs.
     *
     * @param $ids
     * @param $new_status
     * @return DbResult
     */
    public static function changeBilableStatusByIds($ids, $new_status)
    {
        return DB::execute('UPDATE expenses SET billable_status = ? WHERE id IN (?)', $new_status, $ids);
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $expense = parent::create($attributes, $save, false);

        return DataObjectPool::announce($expense, DataObjectPool::OBJECT_CREATED, $attributes);
    }

    public static function preloadDetailsByIds(array $expenses_ids)
    {
        DataObjectPool::getByIds(Expense::class, $expenses_ids);
    }
}
