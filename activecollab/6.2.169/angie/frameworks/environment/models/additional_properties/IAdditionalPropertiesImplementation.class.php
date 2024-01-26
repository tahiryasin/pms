<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Raw additional properties implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
trait IAdditionalPropertiesImplementation
{
    /**
     * Cached log attributes value.
     *
     * @var array
     */
    private $additional_properties = false;

    /**
     * Returna attribute value.
     *
     * @param  string              $name
     * @param  mixed               $default
     * @return mixed
     * @throws NotImplementedError
     */
    public function getAdditionalProperty($name, $default = null)
    {
        $additional_properties = $this->getAdditionalProperties();

        return $additional_properties ? array_var($additional_properties, $name, $default) : $default;
    }

    /**
     * Return additional log properties as array.
     *
     * @return array
     */
    public function getAdditionalProperties()
    {
        if ($this->additional_properties === false) {
            $raw = trim($this->getRawAdditionalProperties());
            $this->additional_properties = empty($raw) ? [] : unserialize($raw);

            if (!is_array($this->additional_properties)) {
                $this->additional_properties = [];
            }
        }

        return $this->additional_properties;
    }

    /**
     * Set attributes value.
     *
     * @param  mixed      $value
     * @return array|null
     */
    public function setAdditionalProperties($value)
    {
        $this->additional_properties = false; // Reset...

        if (empty($value)) {
            return $this->setRawAdditionalProperties(null);
        } else {
            $this->setRawAdditionalProperties(serialize($value));

            return $value;
        }
    }

    /**
     * Get raw additional properties value.
     *
     * @return string
     */
    abstract public function getRawAdditionalProperties();

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Set attribute value.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    public function setAdditionalProperty($name, $value)
    {
        $additional_properties = $this->getAdditionalProperties();

        if ($value === null) {
            if (isset($additional_properties[$name])) {
                unset($additional_properties[$name]);
            }
        } else {
            $additional_properties[$name] = $value;
        }

        $this->setAdditionalProperties($additional_properties);

        return $value;
    }

    /**
     * Set raw additional properties value.
     *
     * @param  string $value
     * @return string
     */
    abstract public function setRawAdditionalProperties($value);
}
