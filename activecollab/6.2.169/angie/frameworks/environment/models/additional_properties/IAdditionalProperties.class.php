<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Interface that is implemented by classes which support additional properties field.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
interface IAdditionalProperties
{
    /**
     * Return additional log properties as array.
     *
     * @return array
     * @throws NotImplementedError
     */
    public function getAdditionalProperties();

    /**
     * Set attributes value.
     *
     * @param  mixed               $value
     * @return mixed
     * @throws NotImplementedError
     */
    public function setAdditionalProperties($value);

    /**
     * Returna attribute value.
     *
     * @param  string              $name
     * @param  mixed               $default
     * @return mixed
     * @throws NotImplementedError
     */
    public function getAdditionalProperty($name, $default = null);

    /**
     * Set attribute value.
     *
     * @param  string              $name
     * @param  mixed               $value
     * @return mixed
     * @throws NotImplementedError
     */
    public function setAdditionalProperty($name, $value);
}
