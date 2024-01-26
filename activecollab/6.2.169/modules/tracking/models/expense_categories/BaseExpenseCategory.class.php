<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseExpenseCategory class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
abstract class BaseExpenseCategory extends ApplicationObject implements IArchive, ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface
{
    const MODEL_NAME = 'ExpenseCategory';
    const MANAGER_NAME = 'ExpenseCategories';

    use IArchiveImplementation;
    use IResetInitialSettingsTimestamp;
    use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'expense_categories';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'is_default', 'is_archived'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'is_default' => false, 'is_archived' => false];

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
            return $underscore ? 'expense_category' : 'ExpenseCategory';
        } else {
            return $underscore ? 'expense_categories' : 'ExpenseCategories';
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
     * Return value of is_archived field.
     *
     * @return bool
     */
    public function getIsArchived()
    {
        return $this->getFieldValue('is_archived');
    }

    /**
     * Set value of is_archived field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsArchived($value)
    {
        return $this->setFieldValue('is_archived', $value);
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
                case 'is_default':
                    return parent::setFieldValue($name, (bool) $value);
                case 'is_archived':
                    return parent::setFieldValue($name, (bool) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
