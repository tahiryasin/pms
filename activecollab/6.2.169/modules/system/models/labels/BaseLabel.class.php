<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseLabel class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseLabel extends ApplicationObject implements ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface, IUpdatedOn
{
    const MODEL_NAME = 'Label';
    const MANAGER_NAME = 'Labels';

    use IResetInitialSettingsTimestamp;
    use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
    use IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'labels';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'type', 'name', 'color', 'updated_on', 'is_default', 'position', 'is_global'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'is_default' => false, 'position' => 0, 'is_global' => false];

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
            return $underscore ? 'label' : 'Label';
        } else {
            return $underscore ? 'labels' : 'Labels';
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
     * Return value of type field.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getFieldValue('type');
    }

    /**
     * Set value of type field.
     *
     * @param  string $value
     * @return string
     */
    public function setType($value)
    {
        return $this->setFieldValue('type', $value);
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
     * Return value of color field.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->getFieldValue('color');
    }

    /**
     * Set value of color field.
     *
     * @param  string $value
     * @return string
     */
    public function setColor($value)
    {
        return $this->setFieldValue('color', $value);
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
     * Return value of position field.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->getFieldValue('position');
    }

    /**
     * Set value of position field.
     *
     * @param  int $value
     * @return int
     */
    public function setPosition($value)
    {
        return $this->setFieldValue('position', $value);
    }

    /**
     * Return value of is_global field.
     *
     * @return bool
     */
    public function getIsGlobal()
    {
        return $this->getFieldValue('is_global');
    }

    /**
     * Set value of is_global field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsGlobal($value)
    {
        return $this->setFieldValue('is_global', $value);
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
                case 'type':
                    return parent::setFieldValue($name, (string) $value);
                case 'name':
                    return parent::setFieldValue($name, (string) $value);
                case 'color':
                    return parent::setFieldValue($name, (string) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'is_default':
                    return parent::setFieldValue($name, (bool) $value);
                case 'position':
                    return parent::setFieldValue($name, (int) $value);
                case 'is_global':
                    return parent::setFieldValue($name, (bool) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
