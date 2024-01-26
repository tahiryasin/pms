<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseBudgetThresholdsNotification class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
abstract class BaseBudgetThresholdsNotification extends ApplicationObject
{
    const MODEL_NAME = 'BudgetThresholdsNotification';
    const MANAGER_NAME = 'BudgetThresholdsNotifications';

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'budget_thresholds_notifications';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'parent_id', 'user_id', 'sent_at'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['parent_id' => 0, 'user_id' => 0];

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
            return $underscore ? 'budget_thresholds_notification' : 'BudgetThresholdsNotification';
        } else {
            return $underscore ? 'budget_thresholds_notifications' : 'BudgetThresholdsNotifications';
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
     * Return value of sent_at field.
     *
     * @return DateTimeValue
     */
    public function getSentAt()
    {
        return $this->getFieldValue('sent_at');
    }

    /**
     * Set value of sent_at field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    public function setSentAt($value)
    {
        return $this->setFieldValue('sent_at', $value);
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
                case 'parent_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'user_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'sent_at':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
