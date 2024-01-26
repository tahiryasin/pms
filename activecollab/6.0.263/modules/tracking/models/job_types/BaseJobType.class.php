<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseJobType class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
abstract class BaseJobType extends ApplicationObject implements IArchive, ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface, IUpdatedOn
{
    use IArchiveImplementation, IResetInitialSettingsTimestamp, ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation, IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'job_types';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'default_hourly_rate', 'is_default', 'is_archived', 'updated_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'default_hourly_rate' => 0.0, 'is_default' => false, 'is_archived' => false];

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
            return $underscore ? 'job_type' : 'JobType';
        } else {
            return $underscore ? 'job_types' : 'JobTypes';
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
     * Return value of default_hourly_rate field.
     *
     * @return float
     */
    public function getDefaultHourlyRate()
    {
        return $this->getFieldValue('default_hourly_rate');
    }

    /**
     * Set value of default_hourly_rate field.
     *
     * @param  float $value
     * @return float
     */
    public function setDefaultHourlyRate($value)
    {
        return $this->setFieldValue('default_hourly_rate', $value);
    }

    /**
     * Return value of is_default field.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->getFieldValue('is_default');
    }

    /**
     * Set value of is_default field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsDefault($value)
    {
        return $this->setFieldValue('is_default', $value);
    }

    /**
     * Return value of is_archived field.
     *
     * @return bool
     */
    public function getIsArchived()
    {
        return $this->getFieldValue('is_archived');
    }

    /**
     * Set value of is_archived field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsArchived($value)
    {
        return $this->setFieldValue('is_archived', $value);
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
                case 'default_hourly_rate':
                    return parent::setFieldValue($name, (float) $value);
                case 'is_default':
                    return parent::setFieldValue($name, (bool) $value);
                case 'is_archived':
                    return parent::setFieldValue($name, (bool) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
