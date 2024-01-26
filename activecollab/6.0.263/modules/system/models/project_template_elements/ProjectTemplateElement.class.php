<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Project template element instance.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class ProjectTemplateElement extends BaseProjectTemplateElement
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['template_id'] = $this->getTemplateId();
        $result['position'] = $this->getPosition();

        foreach ($this->getElementProperties() as $property => $cast) {
            if ($cast === 'array') {
                $result[$property] = empty($this->getAdditionalProperty($property)) ? [] : (array) $this->getAdditionalProperty($property);
            } else {
                $result[$property] = call_user_func($cast, $this->getAdditionalProperty($property));
            }
        }

        return $result;
    }

    /**
     * Return array of element properties.
     *
     * Key is name of the property, and value is a casting method
     *
     * @return array
     */
    abstract public function getElementProperties();

    public function getRoutingContext(): string
    {
        return 'project_template_element';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'project_template_id' => $this->getTemplateId(),
            'project_template_element_id' => $this->getId(),
        ];
    }

    /**
     * Returns true if $user can view this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return Projects::canAdd($user);
    }

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if (!$this->validatePresenceOf('template_id')) {
            $errors->addError('Please select a template', 'template_id');
        }

        foreach ($this->getRequiredElementProperties() as $property) {
            if ($property == 'name' || $property == 'body') {
                if (!$this->validatePresenceOf($property)) {
                    $errors->addError("Element $property is required", $property);
                }
            } else {
                if (!$this->getAdditionalProperty($property)) {
                    $errors->addError("Element $property is required", $property);
                }
            }
        }

        if ($this->getAdditionalProperty('start_on') && $this->getAdditionalProperty('due_on') &&
            $this->getAdditionalProperty('start_on') > $this->getAdditionalProperty('due_on')) {
            $errors->addError('Start on should be before due on', 'start_on');
        }

        parent::validate($errors);
    }

    /**
     * Return required element properties.
     *
     * @return array
     */
    public function getRequiredElementProperties()
    {
        return ['name'];
    }

    /**
     * Return template that's been used to create this project.
     *
     * @return ProjectTemplate
     */
    public function getTemplate()
    {
        return DataObjectPool::get('ProjectTemplate', $this->getTemplateId());
    }

    /**
     * Hide attachments from clients for template element.
     *
     * @param bool $is_hidden
     */
    public function hideOrShowAttachmentsFromClients($is_hidden)
    {
        $table = Attachments::getTableName();
        DB::execute("UPDATE {$table} SET is_hidden_from_clients = ? WHERE parent_type = ? AND parent_id = ?", $is_hidden, $this->getType(), $this->getId());
    }
}
