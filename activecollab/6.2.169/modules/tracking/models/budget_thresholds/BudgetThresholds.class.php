<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BudgetThresholds class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class BudgetThresholds extends BaseBudgetThresholds
{
    /**
     * @param  string            $collection_name
     * @param  null              $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user = null)
    {
        $collection = parent::prepareCollection($collection_name, $user);

        if (str_starts_with($collection_name, 'budget_thresholds_for_')) {
            $bits = explode('_', $collection_name);
            $project_id = (int) array_pop($bits);
            $collection->setConditions('project_id = ?', $project_id);
        }

        return $collection;
    }

    public static function create(array $attributes, bool $save = true, bool $announce = true): BudgetThreshold
    {
        /** @var BudgetThreshold $budget_threshold */
        $budget_threshold = parent::create($attributes, $save, $announce);

        return $budget_threshold;
    }

     public static function scrap(DataObject &$instance, $force_delete = false)
     {
         return parent::scrap($instance, $force_delete);
     }
}
