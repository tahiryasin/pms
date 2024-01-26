<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseWebhook class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseWebhook extends ApplicationObject implements ActiveCollab\Foundation\Webhooks\WebhookInterface, ICreatedOn, ICreatedBy
{
    const MODEL_NAME = 'Webhook';
    const MANAGER_NAME = 'Webhooks';

    use ICreatedOnImplementation;
    use ICreatedByImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'webhooks';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'type', 'integration_id', 'name', 'url', 'is_enabled', 'secret', 'filter_event_types', 'filter_projects', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['integration_id' => 0, 'name' => '', 'is_enabled' => false];

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
            return $underscore ? 'webhook' : 'Webhook';
        } else {
            return $underscore ? 'webhooks' : 'Webhooks';
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
     * Return value of integration_id field.
     *
     * @return int
     */
    public function getIntegrationId()
    {
        return $this->getFieldValue('integration_id');
    }

    /**
     * Set value of integration_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setIntegrationId($value)
    {
        return $this->setFieldValue('integration_id', $value);
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
     * Return value of url field.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getFieldValue('url');
    }

    /**
     * Set value of url field.
     *
     * @param  string $value
     * @return string
     */
    public function setUrl($value)
    {
        return $this->setFieldValue('url', $value);
    }

    /**
     * Return value of is_enabled field.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getFieldValue('is_enabled');
    }

    /**
     * Set value of is_enabled field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsEnabled($value)
    {
        return $this->setFieldValue('is_enabled', $value);
    }

    /**
     * Return value of secret field.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->getFieldValue('secret');
    }

    /**
     * Set value of secret field.
     *
     * @param  string $value
     * @return string
     */
    public function setSecret($value)
    {
        return $this->setFieldValue('secret', $value);
    }

    /**
     * Return value of filter_event_types field.
     *
     * @return string
     */
    public function getFilterEventTypes()
    {
        return $this->getFieldValue('filter_event_types');
    }

    /**
     * Set value of filter_event_types field.
     *
     * @param  string $value
     * @return string
     */
    public function setFilterEventTypes($value)
    {
        return $this->setFieldValue('filter_event_types', $value);
    }

    /**
     * Return value of filter_projects field.
     *
     * @return string
     */
    public function getFilterProjects()
    {
        return $this->getFieldValue('filter_projects');
    }

    /**
     * Set value of filter_projects field.
     *
     * @param  string $value
     * @return string
     */
    public function setFilterProjects($value)
    {
        return $this->setFieldValue('filter_projects', $value);
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
                case 'integration_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'name':
                    return parent::setFieldValue($name, (string) $value);
                case 'url':
                    return parent::setFieldValue($name, (string) $value);
                case 'is_enabled':
                    return parent::setFieldValue($name, (bool) $value);
                case 'secret':
                    return parent::setFieldValue($name, (string) $value);
                case 'filter_event_types':
                    return parent::setFieldValue($name, (string) $value);
                case 'filter_projects':
                    return parent::setFieldValue($name, (string) $value);
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'created_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'created_by_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'created_by_email':
                    return parent::setFieldValue($name, (string) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
