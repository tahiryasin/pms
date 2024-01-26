<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseProjectTemplate class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseProjectTemplate extends ApplicationObject implements IMembers, ITrash, ICreatedOn, ICreatedBy, IUpdatedOn
{
    use IMembersViaConnectionTableImplementation, ITrashImplementation, ICreatedOnImplementation, ICreatedByImplementation, IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'project_templates';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'is_trashed', 'trashed_on', 'trashed_by_id'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'is_trashed' => false, 'trashed_by_id' => 0];

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
            return $underscore ? 'project_template' : 'ProjectTemplate';
        } else {
            return $underscore ? 'project_templates' : 'ProjectTemplates';
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
     * Return value of is_trashed field.
     *
     * @return bool
     */
    public function getIsTrashed()
    {
        return $this->getFieldValue('is_trashed');
    }

    /**
     * Set value of is_trashed field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsTrashed($value)
    {
        return $this->setFieldValue('is_trashed', $value);
    }

    /**
     * Return value of trashed_on field.
     *
     * @return DateTimeValue
     */
    public function getTrashedOn()
    {
        return $this->getFieldValue('trashed_on');
    }

    /**
     * Set value of trashed_on field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    public function setTrashedOn($value)
    {
        return $this->setFieldValue('trashed_on', $value);
    }

    /**
     * Return value of trashed_by_id field.
     *
     * @return int
     */
    public function getTrashedById()
    {
        return $this->getFieldValue('trashed_by_id');
    }

    /**
     * Set value of trashed_by_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setTrashedById($value)
    {
        return $this->setFieldValue('trashed_by_id', $value);
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
                case 'is_trashed':
                    return parent::setFieldValue($name, (bool) $value);
                case 'trashed_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'trashed_by_id':
                    return parent::setFieldValue($name, (int) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
