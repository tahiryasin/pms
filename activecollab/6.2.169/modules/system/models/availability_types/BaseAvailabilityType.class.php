<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseAvailabilityType class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseAvailabilityType extends ApplicationObject implements IWhoCanSeeThis, ICreatedOn, IUpdatedOn, AvailabilityTypeInterface
{
    const MODEL_NAME = 'AvailabilityType';
    const MANAGER_NAME = 'AvailabilityTypes';

    use IWhoCanSeeThisImplementation;
    use ICreatedOnImplementation;
    use IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'availability_types';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'level', 'created_on', 'updated_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['level' => 'not_available'];

    /**
     * Primary key fields.
     *
     * @var array
     */
    protected $primary_key = ['id'];

    /**
     * Return name of this model.
     *
     * @param  bool   $underscore
     * @param  bool   $singular
     * @return string
     */
    public function getModelName($underscore = false, $singular = false)
    {
        if ($singular) {
            return $underscore ? 'availability_type' : 'AvailabilityType';
        } else {
            return $underscore ? 'availability_types' : 'AvailabilityTypes';
        }
    }

    /**
     * Name of AI field (if any).
     *
     * @var string
     */
    protected $auto_increment = 'id';
    // ---------------------------------------------------
    //  Fields
    // ---------------------------------------------------

    /**
     * Return value of id field.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getFieldValue('id');
    }

    /**
     * Set value of id field.
     *
     * @param  int $value
     * @return int
     */
    public function setId($value)
    {
        return $this->setFieldValue('id', $value);
    }

    /**
     * Return value of name field.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFieldValue('name');
    }

    /**
     * Set value of name field.
     *
     * @param  string $value
     * @return string
     */
    public function setName($value)
    {
        return $this->setFieldValue('name', $value);
    }

    /**
     * Return value of level field.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->getFieldValue('level');
    }

    /**
     * Set value of level field.
     *
     * @param  string $value
     * @return string
     */
    public function setLevel($value)
    {
        return $this->setFieldValue('level', $value);
    }

    /**
     * Return value of created_on field.
     *
     * @return DateTimeValue
     */
    public function getCreatedOn()
    {
        return $this->getFieldValue('created_on');
    }

    /**
     * Set value of created_on field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    public function setCreatedOn($value)
    {
        return $this->setFieldValue('created_on', $value);
    }

    /**
     * Return value of updated_on field.
     *
     * @return DateTimeValue
     */
    public function getUpdatedOn()
    {
        return $this->getFieldValue('updated_on');
    }

    /**
     * Set value of updated_on field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    public function setUpdatedOn($value)
    {
        return $this->setFieldValue('updated_on', $value);
    }

    /**
     * Set value of specific field.
     *
     * @param  string            $name
     * @param  mixed             $value
     * @return mixed
     * @throws InvalidParamError
     */
    public function setFieldValue($name, $value)
    {
        if ($value === null) {
            return parent::setFieldValue($name, null);
        } else {
            switch ($name) {
                case 'id':
                    return parent::setFieldValue($name, (int) $value);
                case 'name':
                    return parent::setFieldValue($name, (string) $value);
                case 'level':
                    return parent::setFieldValue($name, (empty($value) ? null : (string) $value));
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
