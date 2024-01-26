<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseCategory class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseCategory extends ApplicationObject implements ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface, IChild, ICreatedOn, ICreatedBy, IUpdatedOn
{
    use IResetInitialSettingsTimestamp, IChildImplementation, ICreatedOnImplementation, ICreatedByImplementation, IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'categories';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'type', 'parent_type', 'parent_id', 'name', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => ''];

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
            return $underscore ? 'category' : 'Category';
        } else {
            return $underscore ? 'categories' : 'Categories';
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
     * Return value of parent_type field.
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->getFieldValue('parent_type');
    }

    /**
     * Set value of parent_type field.
     *
     * @param  string $value
     * @return string
     */
    public function setParentType($value)
    {
        return $this->setFieldValue('parent_type', $value);
    }

    /**
     * Return value of parent_id field.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getFieldValue('parent_id');
    }

    /**
     * Set value of parent_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setParentId($value)
    {
        return $this->setFieldValue('parent_id', $value);
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
     * Return value of created_by_id field.
     *
     * @return int
     */
    public function getCreatedById()
    {
        return $this->getFieldValue('created_by_id');
    }

    /**
     * Set value of created_by_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setCreatedById($value)
    {
        return $this->setFieldValue('created_by_id', $value);
    }

    /**
     * Return value of created_by_name field.
     *
     * @return string
     */
    public function getCreatedByName()
    {
        return $this->getFieldValue('created_by_name');
    }

    /**
     * Set value of created_by_name field.
     *
     * @param  string $value
     * @return string
     */
    public function setCreatedByName($value)
    {
        return $this->setFieldValue('created_by_name', $value);
    }

    /**
     * Return value of created_by_email field.
     *
     * @return string
     */
    public function getCreatedByEmail()
    {
        return $this->getFieldValue('created_by_email');
    }

    /**
     * Set value of created_by_email field.
     *
     * @param  string $value
     * @return string
     */
    public function setCreatedByEmail($value)
    {
        return $this->setFieldValue('created_by_email', $value);
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
                case 'type':
                    return parent::setFieldValue($name, (string) $value);
                case 'parent_type':
                    return parent::setFieldValue($name, (string) $value);
                case 'parent_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'name':
                    return parent::setFieldValue($name, (string) $value);
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'created_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'created_by_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'created_by_email':
                    return parent::setFieldValue($name, (string) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
