<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric;

use Angie\Inflector;
use Angie\Metric\Result\ResultInterface;
use DateValue;
use LogicException;

/**
 * @package Angie\Metric
 */
abstract class Metric implements MetricInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $bits = explode('\\', get_class($this));
        $class_name = array_pop($bits);

        if (str_ends_with($class_name, $this->getClassNameSufix())) {
            return Inflector::underscore(substr($class_name, 0, strlen($class_name) - strlen($this->getClassNameSufix())));
        } else {
            return Inflector::underscore($class_name);
        }
    }

    /**
     * Return class name sufix.
     *
     * This sufix will be removed when class name is automatically converted to metric name.
     *
     * @return string
     */
    protected function getClassNameSufix()
    {
        return '';
    }

    /**
     * Return class name of the result class.
     *
     * @return string
     */
    abstract protected function getResultClassName();

    /**
     * Produce metric result instance based on metric result and date.
     *
     * @param  mixed           $result
     * @param  DateValue       $date
     * @return ResultInterface
     */
    protected function produceResult($result, DateValue $date)
    {
        $result_class_name = $this->getResultClassName();

        if (empty($result_class_name)) {
            throw new LogicException('Metric needs to return a proper result class name');
        }

        return new $result_class_name($this->getName(), $result, $date);
    }

    /**
     * Return date time range, as array of from - to timestamp strings.
     *
     * @param  DateValue $date
     * @return array
     */
    protected function dateToRange(DateValue $date)
    {
        $day = $date->format('Y-m-d');

        return [
            "{$day} 00:00:00",
            "{$day} 23:59:59",
        ];
    }
}
