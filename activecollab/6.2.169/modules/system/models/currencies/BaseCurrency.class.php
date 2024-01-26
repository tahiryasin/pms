<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseCurrency class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseCurrency extends ApplicationObject implements ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface, IUpdatedOn
{
    const MODEL_NAME = 'Currency';
    const MANAGER_NAME = 'Currencies';

    use IResetInitialSettingsTimestamp;
    use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
    use IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'currencies';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'code', 'symbol', 'symbol_native', 'decimal_spaces', 'decimal_rounding', 'is_default', 'updated_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'decimal_spaces' => 2, 'decimal_rounding' => 0.0, 'is_default' => false];

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
            return $underscore ? 'currency' : 'Currency';
        } else {
            return $underscore ? 'currencies' : 'Currencies';
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
     * Return value of symbol field.
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->getFieldValue('symbol');
    }

    /**
     * Set value of symbol field.
     *
     * @param  string $value
     * @return string
     */
    public function setSymbol($value)
    {
        return $this->setFieldValue('symbol', $value);
    }

    /**
     * Return value of symbol_native field.
     *
     * @return string
     */
    public function getSymbolNative()
    {
        return $this->getFieldValue('symbol_native');
    }

    /**
     * Set value of symbol_native field.
     *
     * @param  string $value
     * @return string
     */
    public function setSymbolNative($value)
    {
        return $this->setFieldValue('symbol_native', $value);
    }

    /**
     * Return value of decimal_spaces field.
     *
     * @return int
     */
    public function getDecimalSpaces()
    {
        return $this->getFieldValue('decimal_spaces');
    }

    /**
     * Set value of decimal_spaces field.
     *
     * @param  int $value
     * @return int
     */
    public function setDecimalSpaces($value)
    {
        return $this->setFieldValue('decimal_spaces', $value);
    }

    /**
     * Return value of decimal_rounding field.
     *
     * @return float
     */
    public function getDecimalRounding()
    {
        return $this->getFieldValue('decimal_rounding');
    }

    /**
     * Set value of decimal_rounding field.
     *
     * @param  float $value
     * @return float
     */
    public function setDecimalRounding($value)
    {
        return $this->setFieldValue('decimal_rounding', $value);
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
                case 'code':
                    return parent::setFieldValue($name, (string) $value);
                case 'symbol':
                    return parent::setFieldValue($name, (string) $value);
                case 'symbol_native':
                    return parent::setFieldValue($name, (string) $value);
                case 'decimal_spaces':
                    return parent::setFieldValue($name, (int) $value);
                case 'decimal_rounding':
                    return parent::setFieldValue($name, (float) $value);
                case 'is_default':
                    return parent::setFieldValue($name, (bool) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
