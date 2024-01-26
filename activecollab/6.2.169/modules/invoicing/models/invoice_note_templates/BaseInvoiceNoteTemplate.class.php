<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseInvoiceNoteTemplate class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
abstract class BaseInvoiceNoteTemplate extends ApplicationObject implements ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface
{
    const MODEL_NAME = 'InvoiceNoteTemplate';
    const MANAGER_NAME = 'InvoiceNoteTemplates';

    use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'invoice_note_templates';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'name', 'content', 'is_default', 'position'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['name' => '', 'is_default' => false, 'position' => 0];

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
            return $underscore ? 'invoice_note_template' : 'InvoiceNoteTemplate';
        } else {
            return $underscore ? 'invoice_note_templates' : 'InvoiceNoteTemplates';
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
     * Return value of content field.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getFieldValue('content');
    }

    /**
     * Set value of content field.
     *
     * @param  string $value
     * @return string
     */
    public function setContent($value)
    {
        return $this->setFieldValue('content', $value);
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
     * Return value of position field.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->getFieldValue('position');
    }

    /**
     * Set value of position field.
     *
     * @param  int $value
     * @return int
     */
    public function setPosition($value)
    {
        return $this->setFieldValue('position', $value);
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
                case 'content':
                    return parent::setFieldValue($name, (string) $value);
                case 'is_default':
                    return parent::setFieldValue($name, (bool) $value);
                case 'position':
                    return parent::setFieldValue($name, (int) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
