<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Reports\Report;

use Angie\Reports\Report;
use IAdditionalProperties;
use InvalidParamError;
use NotImplementedError;

/**
 * Base report implementation.
 *
 * @package Angie\Search
 */
trait Implementation
{
    /**
     * Return an array of columns that can be used to group the result.
     *
     * @return array|false
     */
    public function canBeGroupedBy()
    {
        return false;
    }

    /**
     * Return max level of result grouping.
     *
     * @return int
     */
    public function getGroupingMaxLevel()
    {
        return 1;
    }

    /**
     * Return true if result of this report is grouped.
     *
     * @return bool
     */
    public function isGrouped()
    {
        $group_by = $this->getGroupBy();

        return array_shift($group_by) != Report::DONT_GROUP;
    }

    /**
     * Return array of properties that this report should be grouped by.
     *
     * @return array
     * @throws NotImplementedError
     */
    public function getGroupBy()
    {
        if ($this instanceof IAdditionalProperties) {
            return (array) $this->getAdditionalProperty('group_by', [Report::DONT_GROUP]);
        } else {
            throw new NotImplementedError(__METHOD__);
        }
    }

    /**
     * Set group by.
     *
     * @return array
     * @throws InvalidParamError
     * @throws NotImplementedError
     */
    public function setGroupBy()
    {
        if ($this instanceof IAdditionalProperties) {
            $args_num = func_num_args();

            if ($args_num === 1) {
                $arg_value = func_get_arg(0);

                if (is_array($arg_value)) {
                    $group_by = $arg_value;
                } elseif (strpos($arg_value, ',') !== false) {
                    $group_by = explode(',', $arg_value);
                } elseif ($arg_value) {
                    $group_by = [$arg_value];
                } else {
                    $group_by = [Report::DONT_GROUP];
                }
            } elseif ($args_num > 1) {
                $group_by = func_get_args();
            } else {
                $group_by = [Report::DONT_GROUP];
            }

            $group_by = array_unique($group_by);

            if (count($group_by) > $this->getGroupingMaxLevel()) {
                throw new InvalidParamError('group_by', $group_by, 'Max levels of grouping is ' . $this->getGroupingMaxLevel());
            }

            return $this->setAdditionalProperty('group_by', $group_by);
        } else {
            throw new NotImplementedError(__METHOD__);
        }
    }

    /**
     * Reset group by settings.
     */
    public function ungroup()
    {
        $this->setGroupBy([Report::DONT_GROUP]);
    }
}
