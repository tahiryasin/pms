<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseNotification class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseNotification extends ApplicationObject implements ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface, IChild, ICreatedOn, IAdditionalProperties
{
    const MODEL_NAME = 'Notification';
    const MANAGER_NAME = 'Notifications';

    use IChildImplementation;
    use ICreatedOnImplementation;
    use IAdditionalPropertiesImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'notifications';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'type', 'parent_type', 'parent_id', 'sender_id', 'sender_name', 'sender_email', 'created_on', 'raw_additional_properties'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = [];

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
            return $underscore ? 'notification' : 'Notification';
        } else {
            return $underscore ? 'notifications' : 'Notifications';
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
     * Return value of sender_id field.
     *
     * @return int
     */
    public function getSenderId()
    {
        return $this->getFieldValue('sender_id');
    }

    /**
     * Set value of sender_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setSenderId($value)
    {
        return $this->setFieldValue('sender_id', $value);
    }

    /**
     * Return value of sender_name field.
     *
     * @return string
     */
    public function getSenderName()
    {
        return $this->getFieldValue('sender_name');
    }

    /**
     * Set value of sender_name field.
     *
     * @param  string $value
     * @return string
     */
    public function setSenderName($value)
    {
        return $this->setFieldValue('sender_name', $value);
    }

    /**
     * Return value of sender_email field.
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->getFieldValue('sender_email');
    }

    /**
     * Set value of sender_email field.
     *
     * @param  string $value
     * @return string
     */
    public function setSenderEmail($value)
    {
        return $this->setFieldValue('sender_email', $value);
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
     * Return value of raw_additional_properties field.
     *
     * @return string
     */
    public function getRawAdditionalProperties()
    {
        return $this->getFieldValue('raw_additional_properties');
    }

    /**
     * Set value of raw_additional_properties field.
     *
     * @param  string $value
     * @return string
     */
    public function setRawAdditionalProperties($value)
    {
        return $this->setFieldValue('raw_additional_properties', $value);
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
                case 'sender_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'sender_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'sender_email':
                    return parent::setFieldValue($name, (string) $value);
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'raw_additional_properties':
                    return parent::setFieldValue($name, (string) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
