<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseUserInvitation class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseUserInvitation extends ApplicationObject implements ICreatedOn, ICreatedBy, IUpdatedOn
{
    use ICreatedOnImplementation, ICreatedByImplementation, IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'user_invitations';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'user_id', 'invited_to_type', 'invited_to_id', 'code', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['user_id' => 0, 'code' => ''];

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
            return $underscore ? 'user_invitation' : 'UserInvitation';
        } else {
            return $underscore ? 'user_invitations' : 'UserInvitations';
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
     * Return value of user_id field.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->getFieldValue('user_id');
    }

    /**
     * Set value of user_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setUserId($value)
    {
        return $this->setFieldValue('user_id', $value);
    }

    /**
     * Return value of invited_to_type field.
     *
     * @return string
     */
    public function getInvitedToType()
    {
        return $this->getFieldValue('invited_to_type');
    }

    /**
     * Set value of invited_to_type field.
     *
     * @param  string $value
     * @return string
     */
    public function setInvitedToType($value)
    {
        return $this->setFieldValue('invited_to_type', $value);
    }

    /**
     * Return value of invited_to_id field.
     *
     * @return int
     */
    public function getInvitedToId()
    {
        return $this->getFieldValue('invited_to_id');
    }

    /**
     * Set value of invited_to_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setInvitedToId($value)
    {
        return $this->setFieldValue('invited_to_id', $value);
    }

    /**
     * Return value of code field.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getFieldValue('code');
    }

    /**
     * Set value of code field.
     *
     * @param  string $value
     * @return string
     */
    public function setCode($value)
    {
        return $this->setFieldValue('code', $value);
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
                case 'user_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'invited_to_type':
                    return parent::setFieldValue($name, (string) $value);
                case 'invited_to_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'code':
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
