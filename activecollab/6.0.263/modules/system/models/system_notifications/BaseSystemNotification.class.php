<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseSystemNotification class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseSystemNotification extends ApplicationObject implements IAdditionalProperties
{
    use IAdditionalPropertiesImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'system_notifications';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'type', 'recipient_id', 'created_on', 'is_dismissed', 'raw_additional_properties'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['recipient_id' => 0, 'is_dismissed' => false];

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
            return $underscore ? 'system_notification' : 'SystemNotification';
        } else {
            return $underscore ? 'system_notifications' : 'SystemNotifications';
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
     * Return value of recipient_id field.
     *
     * @return int
     */
    public function getRecipientId()
    {
        return $this->getFieldValue('recipient_id');
    }

    /**
     * Set value of recipient_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setRecipientId($value)
    {
        return $this->setFieldValue('recipient_id', $value);
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
     * Return value of is_dismissed field.
     *
     * @return bool
     */
    public function getIsDismissed()
    {
        return $this->getFieldValue('is_dismissed');
    }

    /**
     * Set value of is_dismissed field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsDismissed($value)
    {
        return $this->setFieldValue('is_dismissed', $value);
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
                case 'recipient_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'is_dismissed':
                    return parent::setFieldValue($name, (bool) $value);
                case 'raw_additional_properties':
                    return parent::setFieldValue($name, (string) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
