<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseProjectTemplateTaskDependency class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class BaseProjectTemplateTaskDependency extends ApplicationObject implements ICreatedOn
{
    use ICreatedOnImplementation;

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'project_template_task_dependencies';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'parent_id', 'child_id', 'created_on'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['parent_id' => 0, 'child_id' => 0];

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
            return $underscore ? 'project_template_task_dependency' : 'ProjectTemplateTaskDependency';
        } else {
            return $underscore ? 'project_template_task_dependencies' : 'ProjectTemplateTaskDependencies';
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
     * Return value of child_id field.
     *
     * @return int
     */
    public function getChildId()
    {
        return $this->getFieldValue('child_id');
    }

    /**
     * Set value of child_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setChildId($value)
    {
        return $this->setFieldValue('child_id', $value);
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
                case 'child_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
