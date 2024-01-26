<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseRemoteInvoice class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class BaseRemoteInvoice extends ApplicationObject implements IUpdatedOn, IUpdatedBy, IAdditionalProperties
{
    use IUpdatedOnImplementation, IUpdatedByImplementation, IAdditionalPropertiesImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'remote_invoices';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'type', 'invoice_number', 'client', 'remote_code', 'amount', 'balance', 'updated_on', 'updated_by_id', 'updated_by_name', 'updated_by_email', 'raw_additional_properties'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['amount' => 0.0, 'balance' => 0.0];

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
            return $underscore ? 'remote_invoice' : 'RemoteInvoice';
        } else {
            return $underscore ? 'remote_invoices' : 'RemoteInvoices';
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
     * Return value of invoice_number field.
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->getFieldValue('invoice_number');
    }

    /**
     * Set value of invoice_number field.
     *
     * @param  string $value
     * @return string
     */
    public function setInvoiceNumber($value)
    {
        return $this->setFieldValue('invoice_number', $value);
    }

    /**
     * Return value of client field.
     *
     * @return string
     */
    public function getClient()
    {
        return $this->getFieldValue('client');
    }

    /**
     * Set value of client field.
     *
     * @param  string $value
     * @return string
     */
    public function setClient($value)
    {
        return $this->setFieldValue('client', $value);
    }

    /**
     * Return value of remote_code field.
     *
     * @return string
     */
    public function getRemoteCode()
    {
        return $this->getFieldValue('remote_code');
    }

    /**
     * Set value of remote_code field.
     *
     * @param  string $value
     * @return string
     */
    public function setRemoteCode($value)
    {
        return $this->setFieldValue('remote_code', $value);
    }

    /**
     * Return value of amount field.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getFieldValue('amount');
    }

    /**
     * Set value of amount field.
     *
     * @param  float $value
     * @return float
     */
    public function setAmount($value)
    {
        return $this->setFieldValue('amount', $value);
    }

    /**
     * Return value of balance field.
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->getFieldValue('balance');
    }

    /**
     * Set value of balance field.
     *
     * @param  float $value
     * @return float
     */
    public function setBalance($value)
    {
        return $this->setFieldValue('balance', $value);
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
     * Return value of updated_by_id field.
     *
     * @return int
     */
    public function getUpdatedById()
    {
        return $this->getFieldValue('updated_by_id');
    }

    /**
     * Set value of updated_by_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setUpdatedById($value)
    {
        return $this->setFieldValue('updated_by_id', $value);
    }

    /**
     * Return value of updated_by_name field.
     *
     * @return string
     */
    public function getUpdatedByName()
    {
        return $this->getFieldValue('updated_by_name');
    }

    /**
     * Set value of updated_by_name field.
     *
     * @param  string $value
     * @return string
     */
    public function setUpdatedByName($value)
    {
        return $this->setFieldValue('updated_by_name', $value);
    }

    /**
     * Return value of updated_by_email field.
     *
     * @return string
     */
    public function getUpdatedByEmail()
    {
        return $this->getFieldValue('updated_by_email');
    }

    /**
     * Set value of updated_by_email field.
     *
     * @param  string $value
     * @return string
     */
    public function setUpdatedByEmail($value)
    {
        return $this->setFieldValue('updated_by_email', $value);
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
                case 'invoice_number':
                    return parent::setFieldValue($name, (string) $value);
                case 'client':
                    return parent::setFieldValue($name, (string) $value);
                case 'remote_code':
                    return parent::setFieldValue($name, (string) $value);
                case 'amount':
                    return parent::setFieldValue($name, (float) $value);
                case 'balance':
                    return parent::setFieldValue($name, (float) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'updated_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'updated_by_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'updated_by_email':
                    return parent::setFieldValue($name, (string) $value);
                case 'raw_additional_properties':
                    return parent::setFieldValue($name, (string) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
