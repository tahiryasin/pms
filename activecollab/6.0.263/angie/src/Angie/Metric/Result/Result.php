<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Metric\Result;

use DateValue;
use InvalidArgumentException;

/**
 * @package Angie\Metric\Result
 */
abstract class Result implements ResultInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var DateValue
     */
    private $date;

    /**
     * Result constructor.
     *
     * @param string    $name
     * @param mixed     $value
     * @param DateValue $date
     */
    public function __construct($name, $value, DateValue $date)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Metric name is required');
        }

        $this->name = $name;
        $this->value = $value;
        $this->date = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * {@inheritdoc}
     */
    public function addTo(array &$stats)
    {
        $stats[$this->getName()] = $this->getValue();
    }
}
