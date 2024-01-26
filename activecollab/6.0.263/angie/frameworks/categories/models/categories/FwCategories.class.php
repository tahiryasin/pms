<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level category management implementation.
 *
 * @package angie.frameworks.categories
 * @subpackage models
 */
abstract class FwCategories extends BaseCategories
{
    /**
     * Return true if $user can manage categories of a given type.
     *
     * @param  User                   $user
     * @param  string                 $type
     * @param  ApplicationObject|null $parent
     * @return bool
     */
    public static function canManage(User $user, $type, $parent = null)
    {
        return true;
    }

    // ---------------------------------------------------
    //  Finders
    // ---------------------------------------------------

    /**
     * Return categories based on input parameters.
     *
     * Result can be filtered by parent or type, both or none (all categories)
     *
     * @param  ApplicationObject|ICategoriesContext|null $parent
     * @param  string                                    $type
     * @param  string                                    $name
     * @param  bool                                      $one
     * @return Category|Category[]
     * @throws InvalidInstanceError
     */
    public static function findByParams($parent = null, $type = null, $name = null, $one = false)
    {
        $conditions = [];

        if ($parent) {
            if ($parent instanceof ICategoriesContext) {
                $conditions[] = Categories::parentToCondition($parent);
            } else {
                throw new InvalidInstanceError('parent', $parent, 'ICategoriesContext');
            }
        }

        if ($type) {
            $conditions[] = DB::prepare('(type = ?)', $type);
        }

        if ($name) {
            $conditions[] = DB::prepare('(name = ?)', $name);
        }

        return Categories::find([
            'conditions' => count($conditions) ? implode(' AND ', $conditions) : null,
            'one' => $one,
        ]);
    }

    /**
     * Return category ID - name map based on input parameters.
     *
     * Result can be filtered by parent or type, both or none (all categories)
     *
     * @param  ApplicationObject    $parent
     * @param  string               $type
     * @return array|null
     * @throws InvalidInstanceError
     */
    public static function getIdNameMap($parent = null, $type = null)
    {
        $cache_key = null;

        $conditions = [];
        if ($parent && !$parent->isNew()) {
            if ($parent instanceof ICategoriesContext) {
                $conditions[] = DB::prepare('(parent_type = ? AND parent_id = ?)', get_class($parent), $parent->getId());
            } else {
                throw new InvalidInstanceError('parent', $parent, 'ICategoriesContext');
            }
        }

        if ($type) {
            $conditions[] = DB::prepare('(type IN (?))', $type);
        }

        if (is_string($type)) {
            $cache_key = 'categories_' . strtolower($type);

            if ($parent && !$parent->isNew()) {
                $cached_values = AngieApplication::cache()->getByObject($parent, $cache_key);
            } else {
                $cached_values = AngieApplication::cache()->get($cache_key);
            }

            if ($cached_values) {
                return $cached_values;
            }
        }

        if (count($conditions)) {
            $rows = DB::execute('SELECT id, name FROM categories WHERE ' . implode(' AND ', $conditions) . ' ORDER BY name');
        } else {
            $rows = DB::execute('SELECT id, name FROM categories ORDER BY name');
        }

        if ($rows) {
            $result = [];

            foreach ($rows as $row) {
                $result[(int) $row['id']] = $row['name'];
            }

            if (!is_null($cache_key)) {
                if ($parent) {
                    AngieApplication::cache()->setByObject($parent, $cache_key, $result);
                } else {
                    AngieApplication::cache()->set($cache_key, $result);
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * Return category ID-s by list of category names.
     *
     * @param  array              $names
     * @param  string             $type
     * @param  ICategoriesContext $parent
     * @return array
     */
    public static function getIdsByNames($names, $type, $parent = null)
    {
        if ($names) {
            if ($parent instanceof ICategoriesContext) {
                $ids = DB::executeFirstColumn('SELECT DISTINCT id FROM categories WHERE parent_type = ? AND parent_id = ? AND name IN (?) AND type = ?', get_class($parent), $parent->getId(), $names, $type);
            } else {
                $ids = DB::executeFirstColumn('SELECT DISTINCT id FROM categories WHERE name IN (?) AND type = ?', $names, $type);
            }

            if ($ids) {
                foreach ($ids as $k => $v) {
                    $ids[$k] = (int) $v;
                }
            }

            return $ids;
        }

        return null;
    }

    /**
     * Get category names by IDs.
     *
     * @param  array $ids
     * @return array
     */
    public static function getNamesByIds(array $ids)
    {
        $id_name_map = Categories::getIdNameMap();

        $result = [];

        foreach ($ids as $id) {
            if (isset($id_name_map[$id])) {
                $result[$id] = $id_name_map[$id];
            } else {
                $result[$id] = '--';
            }
        }

        return $result;
    }

    /**
     * Search target context for category with the given name and return its ID
     * if it exists.
     *
     * @param  int                $id_in_source_context
     * @param  ICategoriesContext $target_context
     * @return int
     */
    public static function getMatchingCategoryId($id_in_source_context, ICategoriesContext $target_context)
    {
        $category = DB::executeFirstRow('SELECT type, name FROM categories WHERE id = ?', $id_in_source_context);
        if ($category) {
            $id_in_target_context = DB::executeFirstCell('SELECT id FROM categories WHERE parent_type = ? AND parent_id = ? AND type = ? AND name = ?', get_class($target_context), $target_context->getId(), $category['type'], $category['name']);

            if ($id_in_source_context) {
                return (int) $id_in_target_context;
            }
        }

        return null;
    }

    /**
     * Remove all categories based on category type.
     *
     * @param string $type
     */
    public static function deleteByType($type)
    {
        DB::transact(function () use ($type) {
            DB::execute('DELETE FROM categories WHERE type = ?', $type);
            DB::execute('DELETE FROM modification_logs WHERE parent_type = ?', $type);
        }, 'Delete categories by type @ ' . __CLASS__);
    }

    /**
     * Remove all categories based on category parent.
     *
     * @param int $parent_id
     */
    public static function deleteByParent($parent_id)
    {
        DB::transact(function () use ($parent_id) {
            DB::execute('DELETE FROM categories WHERE parent_id = ?', $parent_id);
            DB::execute('DELETE FROM modification_logs WHERE parent_id = ?', $parent_id);
        }, 'Delete categories by parent ID @ ' . __CLASS__);
    }

    /**
     * @param ICategoriesContext|null $object
     * @param string|null             $type
     */
    public static function dropCache($object = null, $type = null)
    {
        if (is_string($type)) {
            $cache_key = 'categories_' . strtolower($type);

            if ($object instanceof ICategoriesContext) {
                AngieApplication::cache()->removeByObject($object, $cache_key);
            } else {
                AngieApplication::cache()->remove($cache_key);
            }
        }
    }
}
