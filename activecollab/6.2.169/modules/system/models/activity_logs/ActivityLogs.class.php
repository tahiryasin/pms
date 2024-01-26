<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Globalization;
use Angie\NamedList;

/**
 * Application level activity logs manager.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class ActivityLogs extends BaseActivityLogs
{
    const LOGS_PER_PAGE = 50;

    /**
     * Return new collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws InvalidParamError
     * @throws ImpossibleCollectionError
     */
    public static function prepareCollection($collection_name, $user)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        $bits = explode('_', $collection_name);

        $collection->setPagination(array_pop($bits), ActivityLogs::LOGS_PER_PAGE);
        array_pop($bits); // _page_

        // Global
        if (str_starts_with($collection_name, 'activity_logs_for')) {
            $for = DataObjectPool::get(User::class, array_pop($bits));

            if ($for instanceof User && $for->isActive()) {
                $collection->setConditions(self::prepareCollectionConditions($collection_name, $user));
            } else {
                throw new ImpossibleCollectionError('Recipient not found or found but not active');
            }

            // Daily activity logs
        } else {
            if (str_starts_with($collection_name, 'daily_activity_logs_for')) {
                $day = DateValue::makeFromString(array_pop($bits));
                $for = DataObjectPool::get(User::class, array_pop($bits));

                if ($for instanceof User && $for->isActive()) {
                    $user_gmt_offset = Globalization::getUserGmtOffsetOnDate($user, $day);

                    $collection->setConditions(self::prepareCollectionConditions($collection_name, $user) . ' AND ' . DB::prepare('(created_on BETWEEN ? AND ?)', $day->beginningOfDay()->advance(-1 * $user_gmt_offset, false), $day->endOfDay()->advance(-1 * $user_gmt_offset, false)));
                } else {
                    throw new ImpossibleCollectionError('Recipient not found or found but not active');
                }

                // For user
            } elseif (str_starts_with($collection_name, 'activity_logs_by')) {
                $by = DataObjectPool::get(User::class, array_pop($bits));

                if ($by instanceof User) {
                    $conditions = [
                        DB::prepare('(created_by_id = ?)', $by->getId()),
                    ];

                    if (AngieApplication::authentication()->getAuthenticatedUser()->getId() !== $by->getId()) {
                        $day = DateValue::now();
                        $user_gmt_offset = Globalization::getUserGmtOffsetOnDate($user, $day);

                        $conditions[] = DB::prepare('(created_on BETWEEN ? AND ?)', $day->addDays(-60, false)->beginningOfDay()->advance(-1 * $user_gmt_offset, false), $day->endOfDay()->advance(-1 * $user_gmt_offset, false));
                        $conditions[] = self::prepareCollectionConditions($collection_name, $user);
                    }

                    $collection->setConditions(implode(' AND ', $conditions));
                } else {
                    throw new ImpossibleCollectionError('User not found');
                }

                // In context
            } elseif (str_starts_with($collection_name, 'activity_logs_in')) {
                [$in_type, $in_id] = explode('-', array_pop($bits));

                $in = DataObjectPool::get($in_type, $in_id);

                if ($in instanceof ApplicationObject) {
                    $collection->setConditions(self::prepareCollectionConditions($collection_name, $user, $in));
                } else {
                    throw new ImpossibleCollectionError('User not found or found but not active');
                }
            } else {
                throw new InvalidParamError('collection_name', $collection_name);
            }
        }

        return $collection;
    }

    /**
     * Prepare conditions for activity_logs_for collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @param  ApplicationObject|null    $in
     * @return string
     * @throws ImpossibleCollectionError
     */
    protected static function prepareCollectionConditions($collection_name, $user, $in = null)
    {
        $conditions = $ignore_conditions = $contexts = $ignore_contexts = [];

        /*
         * $contexts are an array where key is context name (users, projects/12 etc) and value is either:
         *
         * - TRUE - all objects in that context
         * - INT[] - array of ID-s from a particular context that are visible
         */
        Angie\Events::trigger('on_visible_object_paths', [$user, &$contexts, &$ignore_contexts, &$in]);

        if (count($contexts)) {
            foreach ($contexts as $context => $what_is_visible) {
                if (empty($what_is_visible)) {
                    continue;
                }

                $subcontext_conditions = [];

                if (strpos($context, '*') === false) {
                    $subcontext_conditions[] = DB::prepare('parent_path = ?', $context);
                } else {
                    $subcontext_conditions[] = DB::prepare('parent_path LIKE ?', str_replace('*', '%', $context));
                }

                if ($what_is_visible && is_foreachable($what_is_visible)) {
                    $subcontext_conditions[] = DB::prepare('parent_id IN (?)', $what_is_visible);
                }

                $conditions[] = '(' . implode(' AND ', $subcontext_conditions) . ')';
            }
        }

        if (empty($conditions)) {
            throw new ImpossibleCollectionError(sprintf("Can't prepare collection '$collection_name'."));
        } else {
            $conditions = '(' . implode(' OR ', $conditions) . ')';
        }

        if (count($ignore_contexts)) {
            foreach ($ignore_contexts as $ignore_context => $what_to_ignore) {
                if (empty($what_to_ignore)) {
                    continue;
                }

                $ignore_subcontext_conditions = [];

                if (strpos($ignore_context, '*') === false) {
                    $ignore_subcontext_conditions[] = DB::prepare('parent_path = ?', $ignore_context);
                } else {
                    $ignore_subcontext_conditions[] = DB::prepare('parent_path LIKE ?', str_replace('*', '%', $ignore_context));
                }

                if ($what_to_ignore && is_foreachable($what_to_ignore)) {
                    $ignore_subcontext_conditions[] = DB::prepare('parent_id IN (?)', $what_to_ignore);
                }

                $ignore_conditions[] = '(' . implode(' AND ', $ignore_subcontext_conditions) . ')';
            }
        }

        if (!empty($ignore_conditions)) {
            $conditions .= ' AND NOT (' . implode(' OR ', $ignore_conditions) . ')';
        }

        return $conditions;
    }

    /**
     * Return rebuild actions.
     *
     * @return NamedList
     */
    public static function getRebuildActions()
    {
        $actions = new NamedList();

        Angie\Events::trigger('on_rebuild_activity_logs', [&$actions]);

        return $actions;
    }

    /**
     * Prepare field values for serialization.
     *
     * @param  array $modification_ids
     * @param  array $fields
     * @return array
     */
    public static function prepareFieldValuesForSerialization(array $modification_ids, $fields)
    {
        $result = [];

        $rows = DB::execute(
            'SELECT modification_id, field, old_value, new_value FROM modification_log_values WHERE modification_id IN (?) AND field IN (?)',
            $modification_ids,
            $fields
        );

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $modification_id = $row['modification_id'];

                if (empty($result[$modification_id])) {
                    $result[$modification_id] = [];
                }

                $result[$modification_id][$row['field']] = [unserialize($row['old_value']), unserialize($row['new_value'])];
            }
        }

        return $result;
    }

    /**
     * Delete activity logs by parent.
     *
     * @param IActivityLog $parent
     */
    public static function deleteByParent(IActivityLog &$parent)
    {
        $ids = DB::executeFirstColumn(
            sprintf(
                'SELECT id FROM activity_logs WHERE %s',
                ActivityLogs::parentToCondition($parent)
            )
        );

        if (!empty($ids)) {
            DB::execute('DELETE FROM activity_logs WHERE id IN (?)', $ids);
            ActivityLogs::clearCacheFor($ids);
        }
    }

    /**
     * Delete logged activitys by parent and additional property.
     *
     * @param IActivityLog $parent
     * @param string       $property_name
     * @param mixed        $property_value
     */
    public static function deleteByParentAndAdditionalProperty(IActivityLog $parent, $property_name, $property_value)
    {
        $rows = DB::execute(
            'SELECT id, raw_additional_properties FROM activity_logs WHERE ' . ActivityLogs::parentToCondition($parent)
        );

        if ($rows) {
            $to_delete = [];

            foreach ($rows as $row) {
                if ($row['raw_additional_properties']) {
                    $properties = unserialize($row['raw_additional_properties']);

                    if (is_array($properties) && isset($properties[$property_name]) && $properties[$property_name] == $property_value) {
                        $to_delete[] = $row['id'];
                    }
                }
            }

            if (count($to_delete)) {
                DB::execute('DELETE FROM activity_logs WHERE id IN (?)', $to_delete);

                ActivityLogs::clearCacheFor($to_delete);
            }
        }
    }

    /**
     * Clear activity log entries and reset auto-increment value.
     */
    public static function clear()
    {
        DB::execute('TRUNCATE TABLE activity_logs');
    }
}
