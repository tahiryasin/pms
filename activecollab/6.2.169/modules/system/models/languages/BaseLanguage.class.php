<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseLanguage class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseLanguage extends ApplicationObject implements ActiveCollab\Foundation\Localization\LanguageInterface, ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface, IUpdatedOn
{
    const MODEL_NAME = 'Language';
    const MANAGER_NAME = 'Languages';

    use IResetInitialSettingsTimestamp;
    use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
    use IUpdatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'languages';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'locale', 'decimal_separator', 'thousands_separator', 'is_rtl', 'is_community_translation', 'is_default', 'updated_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'locale' => '', 'decimal_separator' => '.', 'thousands_separator' => ',', 'is_rtl' => false, 'is_community_translation' => false, 'is_default' => false];

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
            return $underscore ? 'language' : 'Language';
        } else {
            return $underscore ? 'languages' : 'Languages';
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
     * Return value of locale field.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getFieldValue('locale');
    }

    /**
     * Set value of locale field.
     *
     * @param  string $value
     * @return string
     */
    public function setLocale($value)
    {
        return $this->setFieldValue('locale', $value);
    }

    /**
     * Return value of decimal_separator field.
     *
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->getFieldValue('decimal_separator');
    }

    /**
     * Set value of decimal_separator field.
     *
     * @param  string $value
     * @return string
     */
    public function setDecimalSeparator($value)
    {
        return $this->setFieldValue('decimal_separator', $value);
    }

    /**
     * Return value of thousands_separator field.
     *
     * @return string
     */
    public function getThousandsSeparator()
    {
        return $this->getFieldValue('thousands_separator');
    }

    /**
     * Set value of thousands_separator field.
     *
     * @param  string $value
     * @return string
     */
    public function setThousandsSeparator($value)
    {
        return $this->setFieldValue('thousands_separator', $value);
    }

    /**
     * Return value of is_rtl field.
     *
     * @return bool
     */
    public function getIsRtl()
    {
        return $this->getFieldValue('is_rtl');
    }

    /**
     * Set value of is_rtl field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsRtl($value)
    {
        return $this->setFieldValue('is_rtl', $value);
    }

    /**
     * Return value of is_community_translation field.
     *
     * @return bool
     */
    public function getIsCommunityTranslation()
    {
        return $this->getFieldValue('is_community_translation');
    }

    /**
     * Set value of is_community_translation field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsCommunityTranslation($value)
    {
        return $this->setFieldValue('is_community_translation', $value);
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
                case 'locale':
                    return parent::setFieldValue($name, (string) $value);
                case 'decimal_separator':
                    return parent::setFieldValue($name, (string) $value);
                case 'thousands_separator':
                    return parent::setFieldValue($name, (string) $value);
                case 'is_rtl':
                    return parent::setFieldValue($name, (bool) $value);
                case 'is_community_translation':
                    return parent::setFieldValue($name, (bool) $value);
                case 'is_default':
                    return parent::setFieldValue($name, (bool) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
