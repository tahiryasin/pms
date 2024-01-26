<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level labels manager implementation.
 *
 * @package angie.frameworks.labels
 * @subpackage models
 */
abstract class FwLabels extends BaseLabels
{
    const LABELS_NAME_MAX_LENGTH = 30;

    /**
     * Reorder labels.
     *
     * @param Label[]|int[] $labels
     */
    public static function reorder($labels)
    {
        if (!empty($labels)) {
            DB::transact(
                function () use ($labels) {
                    $counter = 1;
                    $timestamp = DateTimeValue::now();

                    foreach ($labels as $label) {
                        DB::execute(
                            'UPDATE `labels` SET `position` = ?, `updated_on` = ? WHERE `id` = ?',
                            $counter++,
                            $timestamp,
                            $label instanceof Label ? $label->getId() : $label
                        );
                    }
                },
                'Reordering labels'
            );
        }

        Labels::clearCache();
    }

    /**
     * Returns true if $user can define new labels.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->canManageTasks();
    }

    /**
     * Returns true if $user can reorder labels.
     *
     * @param  IUser $user
     * @return bool
     */
    public static function canReorder(IUser $user)
    {
        return $user->isOwner();
    }

    /**
     * Return ID - name map for a given label type.
     *
     * @param  string $type
     * @return array
     */
    public static function getIdNameMap($type)
    {
        return AngieApplication::cache()->get(
            [
                'models',
                'labels',
                'id_name_map_for_' . $type,
            ],
            function () use ($type) {
                $result = [];

                if ($labels = Labels::findByType($type)) {
                    foreach ($labels as $label) {
                        $result[$label->getId()] = [
                            $label->getName(),
                            'color' => $label->getColor(),
                        ];
                    }
                }

                return $result;
            }
        );
    }

    /**
     * @var array
     */
    private static $details_by_parent = [];

    /**
     * Preload label details for a given type and a list of ID-s.
     *
     * @param string $parent_type
     * @param array  $parent_ids
     */
    public static function preloadDetailsByParents($parent_type, array $parent_ids)
    {
        self::$details_by_parent[$parent_type] = [];

        $rows = DB::execute(
            'SELECT pl.parent_id, l.id, l.name, l.color
                FROM labels AS l LEFT JOIN parents_labels AS pl ON l.id = pl.label_id
                WHERE pl.parent_type = ? AND pl.parent_id IN (?) ORDER BY `name`',
            $parent_type,
            $parent_ids
        );

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (empty(self::$details_by_parent[$parent_type][$row['parent_id']])) {
                    self::$details_by_parent[$parent_type][$row['parent_id']] = [];
                }

                if ($row['color'] && !empty(Label::COLOR_PALETTE[$row['color']])) {
                    $color = $row['color'];
                } else {
                    $color = FwLabel::LABEL_DEFAULT_COLOR;
                }

                self::$details_by_parent[$parent_type][$row['parent_id']][] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'color' => $color,
                    'lighter_text_color' => Label::COLOR_PALETTE[$color]['lighter_text'],
                    'darker_text_color' => Label::COLOR_PALETTE[$color]['darker_text'],
                ];
            }
        }
        if ($zeros = array_diff($parent_ids, array_keys(self::$details_by_parent[$parent_type]))) {
            foreach ($zeros as $parent_with_no_labels) {
                self::$details_by_parent[$parent_type][$parent_with_no_labels] = [];
            }
        }
    }

    /**
     * Reset manager state (between tests for example).
     */
    public static function resetState()
    {
        self::$details_by_parent = [];
    }

    private static $ids_by_parent = [];

    public static function preloadIdsByParents($parent_type, array $parent_ids)
    {
        self::$ids_by_parent[$parent_type] = [];

        $rows = DB::execute(
            'SELECT pl.parent_id, pl.label_id 
             FROM parents_labels pl
             LEFT JOIN labels l
             ON l.id = pl.label_id
             WHERE parent_type = ? AND parent_id IN (?) ORDER BY l.name ASC',
            $parent_type,
            $parent_ids
        );

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if (empty(self::$ids_by_parent[$parent_type][$row['parent_id']])) {
                    self::$ids_by_parent[$parent_type][$row['parent_id']] = [];
                }

                self::$ids_by_parent[$parent_type][$row['parent_id']][] = $row['label_id'];
            }
        }

        if ($zeros = array_diff($parent_ids, array_keys(self::$ids_by_parent[$parent_type]))) {
            foreach ($zeros as $parent_with_no_labels) {
                self::$ids_by_parent[$parent_type][$parent_with_no_labels] = [];
            }
        }
    }

    public static function getIdsByParentTypeAndParentId(string $parent_type, int $parent_id)
    {
        if (isset(self::$ids_by_parent[$parent_type][$parent_id])) {
            return self::$ids_by_parent[$parent_type][$parent_id];
        } else {
            $label_ids = DB::executeFirstColumn(
                'SELECT label_id FROM parents_labels WHERE parent_type = ? AND parent_id = ?',
                $parent_type,
                $parent_id
            );

            return !empty($label_ids) ? $label_ids : [];
        }
    }

    /**
     * @param  DataObject|ILabels $parent
     * @return array
     */
    public static function getDetailsByParent(ILabels $parent)
    {
        $parent_type = get_class($parent);
        $parent_id = $parent->getId();

        if (isset(self::$details_by_parent[$parent_type][$parent_id])) {
            return self::$details_by_parent[$parent_type][$parent_id];
        } else {
            $result = [];

            if ($rows = DB::execute('SELECT l.id, l.name, l.color, l.is_default, l.is_global, l.position FROM labels AS l LEFT JOIN parents_labels AS pl ON l.id = pl.label_id WHERE pl.parent_type = ? AND pl.parent_id = ? ORDER BY name', $parent_type, $parent_id)) {
                foreach ($rows as $row) {
                    if ($row['color'] && !empty(Label::COLOR_PALETTE[strtoupper($row['color'])])) {
                        $color = strtoupper($row['color']);
                    } else {
                        $color = Label::LABEL_DEFAULT_COLOR;
                    }

                    $result[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'color' => $color,
                        'darker_text_color' => Label::COLOR_PALETTE[$color]['darker_text'],
                        'lighter_text_color' => Label::COLOR_PALETTE[$color]['lighter_text'],
                        'is_default' => $row['is_default'],
                        'is_global' => $row['is_global'],
                        'position' => $row['position'],
                        'url_path' => '/labels/' . $row['id'],
                    ];
                }
            }

            return $result;
        }
    }

    /**
     * Return label name.
     *
     * @param  int    $label_id
     * @param  mixed  $default
     * @return string
     */
    public static function getLabelName($label_id, $default = null)
    {
        $names = AngieApplication::cache()->get(['models', 'lables', 'id_name_map'], function () {
            $result = [];

            if ($rows = DB::execute('SELECT id, UPPER(name) AS "name" FROM labels')) {
                foreach ($rows as $row) {
                    $result[$row['id']] = $row['name'];
                }
            }

            return $result;
        });

        return isset($names[$label_id]) ? $names[$label_id] : $default;
    }

    public static function getNamesByIds(array $label_ids): array
    {
        if (!empty($label_ids)) {
            $rows = DB::execute('SELECT `id`, `name` FROM `labels` WHERE `id` IN (?)', $label_ids);

            if (!empty($rows)) {
                $result = [];

                foreach ($rows as $row) {
                    $result[$row['id']] = $row['name'];
                }

                return $result;
            }
        }

        return [];
    }

    /**
     * Return label ID-s by list of label names.
     *
     * @param  array  $names
     * @param  string $type
     * @return array
     */
    public static function getIdsByNames($names, $type)
    {
        if ($names && is_foreachable($names)) {
            return DB::executeFirstColumn('SELECT id FROM labels WHERE name IN (?) AND type = ? ORDER BY position', $names, $type);
        }

        return null;
    }

    /**
     * Return labels by type name.
     *
     * @param  string                $type
     * @return Label[]|DBResult|null
     */
    public static function findByType($type)
    {
        return Labels::find(['conditions' => ['type = ?', $type]]);
    }

    /**
     * Return default label by given type.
     *
     * @param  string           $type
     * @return Label|DataObject
     */
    public static function findDefault($type)
    {
        return DataObjectPool::get('Label', static::findDefaultId($type));
    }

    /**
     * Return ID of the default label.
     *
     * @param  string   $type
     * @return int|null
     */
    public static function findDefaultId($type)
    {
        return AngieApplication::cache()->get(['models', 'labels', "default_{$type}_id"], function () use ($type) {
            return DB::executeFirstCell('SELECT id FROM labels WHERE type = ? AND is_default = ? LIMIT 0, 1', $type, true);
        });
    }

    /**
     * Set $label as default.
     *
     * @param  Label     $label
     * @throws Exception
     */
    public static function setDefault(Label $label)
    {
        if ($label->getIsDefault()) {
            return;
        }

        try {
            DB::beginWork('Setting default label @ ' . __CLASS__);

            $label->setIsDefault(true);
            $label->save();

            DB::execute('UPDATE labels SET is_default = ? WHERE id != ? AND type = ?', false, $label->getId(), get_class($label));

            DB::commit('Default label set @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to set default label @ ' . __CLASS__);
            throw $e;
        }

        Labels::clearCache();
    }

    /**
     * Unset default label for given type.
     *
     * @param  Label     $label
     * @throws Exception
     */
    public static function unsetDefault(Label $label)
    {
        if (!$label->getIsDefault()) {
            return;
        }

        try {
            DB::beginWork('Unsetting default label @ ' . __CLASS__);

            $label->setIsDefault(false);
            $label->save();

            DB::execute('UPDATE labels SET is_default = ? WHERE id != ? AND type = ?', false, $label->getId(), get_class($label));

            DB::commit('Default label unset @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to unset default label @ ' . __CLASS__);
            throw $e;
        }
    }

    // ---------------------------------------------------
    //  Subquery
    // ---------------------------------------------------

    /**
     * Return ID-s of objects that have one or all of the provided labels.
     *
     * @param  string $parent_type
     * @param  string $label_type
     * @param  array  $label_names
     * @param  bool   $must_have_all_labels
     * @return int[]
     */
    public static function getParentIdsByLabels($parent_type, $label_type, $label_names, $must_have_all_labels = true)
    {
        if ($label_names && is_foreachable($label_names)) {
            $result = DB::executeFirstColumn(Labels::getParentIdsByLabelsSqlQuery($parent_type, $label_type, $label_names, $must_have_all_labels));
        }

        return empty($result) ? [] : $result;
    }

    /**
     * Prepare SQL that will query for one or more of the labels.
     *
     * @param  string $parent_type
     * @param  string $label_type
     * @param  array  $label_names
     * @param  bool   $must_have_all_labels
     * @return string
     */
    public static function getParentIdsByLabelsSqlQuery($parent_type, $label_type, $label_names, $must_have_all_labels = true)
    {
        if ($must_have_all_labels) {
            return DB::prepare('SELECT DISTINCT pl.parent_id AS id FROM parents_labels AS pl LEFT JOIN labels AS l ON pl.label_id = l.id WHERE l.type = ? AND l.name IN (?) AND pl.parent_type = ? GROUP BY pl.parent_id HAVING COUNT(l.name) = ?', $label_type, $label_names, $parent_type, count($label_names));
        } else {
            return DB::prepare('SELECT DISTINCT pl.parent_id AS id FROM parents_labels AS pl LEFT JOIN labels AS l ON pl.label_id = l.id WHERE l.type = ? AND l.name IN (?) AND pl.parent_type = ? ORDER BY pl.parent_id', $label_type, $label_names, $parent_type);
        }
    }

    /**
     * Return ID-s of objects that don't have a label.
     *
     * @param  string $parent_table
     * @param  string $parent_type
     * @return int[]
     */
    public static function getParentIdsWithNoLabels($parent_table, $parent_type)
    {
        $result = DB::executeFirstColumn(Labels::getParentIdsWithNoLabelsSqlQuery($parent_table, $parent_type));

        return empty($result) ? [] : $result;
    }

    /**
     * Prepare query that will return all unlabeled entries from a given table.
     *
     * @param  string $parent_table
     * @param  string $parent_type
     * @return string
     */
    public static function getParentIdsWithNoLabelsSqlQuery($parent_table, $parent_type)
    {
        return DB::prepare("SELECT id FROM $parent_table WHERE NOT EXISTS (SELECT * FROM parents_labels AS pl WHERE pl.parent_type = ? AND $parent_table.id = pl.parent_id)", $parent_type);
    }

    /**
     * Return existing label.
     *
     * @param  array                 $attributes
     * @return Label|DataObject|null
     * @throws InvalidParamError
     */
    private static function getExistingLabelByAttributes(array $attributes)
    {
        if ($existing_label_id = DB::executeFirstCell('SELECT id FROM labels WHERE name = UPPER(?) AND type = ?', array_var($attributes, 'name'), array_var($attributes, 'type'))) {
            return Labels::findById($existing_label_id);
        }

        return null;
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        if ($existing_label = self::getExistingLabelByAttributes($attributes)) {
            return parent::update(
                $existing_label,
                array_merge(
                    $attributes,
                    [
                        'is_global' => true,
                    ]
                ),
                $save
            );
        }

        return parent::create($attributes, $save, $announce);
    }

    /**
     * Update label instance.
     *
     * @param  DataObject $instance
     * @param  array      $attributes
     * @param  bool       $save
     * @return DataObject
     * @throws Exception
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        if (isset($attributes['name']) && strtolower_utf($attributes['name']) != strtolower_utf($instance->getName()) && $existing_label = self::getExistingLabelByAttributes($attributes)) {
            try {
                DB::beginWork('Update existing label @ ' . __CLASS__);

                DB::execute('UPDATE parents_labels SET label_id = ? WHERE label_id = ?', $existing_label->getId(), $instance->getId());
                parent::update($existing_label, array_merge($attributes, ['is_global' => true]), $save);
                parent::scrap($instance, true);
                AngieApplication::cache()->removeByObject($instance);

                DB::commit('Existing label updated @ ' . __CLASS__);

                return $existing_label;
            } catch (Exception $e) {
                DB::rollback('Failed to update existing label @ ' . __CLASS__);
                throw $e;
            }
        }

        return parent::update($instance, $attributes, $save);
    }
}
