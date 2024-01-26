<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * BaseRecurringTask class.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
abstract class BaseRecurringTask extends ApplicationObject implements IAssignees, IHistory, IAccessLog, ISubscriptions, IAttachments, ILabels, \Angie\Search\SearchItem\SearchItemInterface, ITrash, IActivityLog, IHiddenFromClients, ISubtasks, IProjectElement, IBody, ICreatedOn, ICreatedBy, IUpdatedOn, IUpdatedBy, IAdditionalProperties
{
    use IAssigneesImplementation, IHistoryImplementation, IAccessLogImplementation, ISubscriptionsImplementation, IAttachmentsImplementation, ILabelsImplementation, \Angie\Search\SearchItem\Implementation, ITrashImplementation, IActivityLogImplementation, ISubtasksImplementation, IProjectElementImplementation, IBodyImplementation, ICreatedOnImplementation, ICreatedByImplementation, IUpdatedOnImplementation, IUpdatedByImplementation, IAdditionalPropertiesImplementation {
        IProjectElementImplementation::canViewAccessLogs insteadof IAccessLogImplementation;
        IProjectElementImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation;
    }

    /**
     * Name of the table where records are stored.
     *
     * @var string
     */
    protected $table_name = 'recurring_tasks';

    /**
     * All table fields.
     *
     * @var array
     */
    protected $fields = ['id', 'project_id', 'task_list_id', 'assignee_id', 'delegated_by_id', 'name', 'body', 'is_important', 'created_on', 'created_by_id', 'created_by_name', 'created_by_email', 'updated_on', 'updated_by_id', 'updated_by_name', 'updated_by_email', 'start_in', 'due_in', 'job_type_id', 'estimate', 'position', 'is_hidden_from_clients', 'is_trashed', 'original_is_trashed', 'trashed_on', 'trashed_by_id', 'repeat_frequency', 'repeat_amount', 'repeat_amount_extended', 'triggered_number', 'last_trigger_on', 'fake_assignee_name', 'fake_assignee_email', 'raw_additional_properties'];

    /**
     * Default field values.
     *
     * @var array
     */
    protected $default_field_values = ['project_id' => 0, 'task_list_id' => 0, 'assignee_id' => 0, 'delegated_by_id' => 0, 'name' => '', 'is_important' => false, 'job_type_id' => 0, 'estimate' => 0.0, 'position' => 0, 'is_hidden_from_clients' => false, 'is_trashed' => false, 'original_is_trashed' => false, 'trashed_by_id' => 0, 'repeat_frequency' => 'never', 'repeat_amount' => 0, 'repeat_amount_extended' => 0, 'triggered_number' => 0];

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
            return $underscore ? 'recurring_task' : 'RecurringTask';
        } else {
            return $underscore ? 'recurring_tasks' : 'RecurringTasks';
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
     * Return value of project_id field.
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->getFieldValue('project_id');
    }

    /**
     * Set value of project_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setProjectId($value)
    {
        return $this->setFieldValue('project_id', $value);
    }

    /**
     * Return value of task_list_id field.
     *
     * @return int
     */
    public function getTaskListId()
    {
        return $this->getFieldValue('task_list_id');
    }

    /**
     * Set value of task_list_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setTaskListId($value)
    {
        return $this->setFieldValue('task_list_id', $value);
    }

    /**
     * Return value of assignee_id field.
     *
     * @return int
     */
    public function getAssigneeId()
    {
        return $this->getFieldValue('assignee_id');
    }

    /**
     * Set value of assignee_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setAssigneeId($value)
    {
        return $this->setFieldValue('assignee_id', $value);
    }

    /**
     * Return value of delegated_by_id field.
     *
     * @return int
     */
    public function getDelegatedById()
    {
        return $this->getFieldValue('delegated_by_id');
    }

    /**
     * Set value of delegated_by_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setDelegatedById($value)
    {
        return $this->setFieldValue('delegated_by_id', $value);
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
     * Return value of body field.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->getFieldValue('body');
    }

    /**
     * Set value of body field.
     *
     * @param  string $value
     * @return string
     */
    public function setBody($value)
    {
        return $this->setFieldValue('body', $value);
    }

    /**
     * Return value of is_important field.
     *
     * @return bool
     */
    public function getIsImportant()
    {
        return $this->getFieldValue('is_important');
    }

    /**
     * Set value of is_important field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsImportant($value)
    {
        return $this->setFieldValue('is_important', $value);
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
     * Return value of start_in field.
     *
     * @return int
     */
    public function getStartIn()
    {
        return $this->getFieldValue('start_in');
    }

    /**
     * Set value of start_in field.
     *
     * @param  int $value
     * @return int
     */
    public function setStartIn($value)
    {
        return $this->setFieldValue('start_in', $value);
    }

    /**
     * Return value of due_in field.
     *
     * @return int
     */
    public function getDueIn()
    {
        return $this->getFieldValue('due_in');
    }

    /**
     * Set value of due_in field.
     *
     * @param  int $value
     * @return int
     */
    public function setDueIn($value)
    {
        return $this->setFieldValue('due_in', $value);
    }

    /**
     * Return value of job_type_id field.
     *
     * @return int
     */
    public function getJobTypeId()
    {
        return $this->getFieldValue('job_type_id');
    }

    /**
     * Set value of job_type_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setJobTypeId($value)
    {
        return $this->setFieldValue('job_type_id', $value);
    }

    /**
     * Return value of estimate field.
     *
     * @return float
     */
    public function getEstimate()
    {
        return $this->getFieldValue('estimate');
    }

    /**
     * Set value of estimate field.
     *
     * @param  float $value
     * @return float
     */
    public function setEstimate($value)
    {
        return $this->setFieldValue('estimate', $value);
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
     * Return value of is_hidden_from_clients field.
     *
     * @return bool
     */
    public function getIsHiddenFromClients()
    {
        return $this->getFieldValue('is_hidden_from_clients');
    }

    /**
     * Set value of is_hidden_from_clients field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsHiddenFromClients($value)
    {
        return $this->setFieldValue('is_hidden_from_clients', $value);
    }

    /**
     * Return value of is_trashed field.
     *
     * @return bool
     */
    public function getIsTrashed()
    {
        return $this->getFieldValue('is_trashed');
    }

    /**
     * Set value of is_trashed field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setIsTrashed($value)
    {
        return $this->setFieldValue('is_trashed', $value);
    }

    /**
     * Return value of original_is_trashed field.
     *
     * @return bool
     */
    public function getOriginalIsTrashed()
    {
        return $this->getFieldValue('original_is_trashed');
    }

    /**
     * Set value of original_is_trashed field.
     *
     * @param  bool $value
     * @return bool
     */
    public function setOriginalIsTrashed($value)
    {
        return $this->setFieldValue('original_is_trashed', $value);
    }

    /**
     * Return value of trashed_on field.
     *
     * @return DateTimeValue
     */
    public function getTrashedOn()
    {
        return $this->getFieldValue('trashed_on');
    }

    /**
     * Set value of trashed_on field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    public function setTrashedOn($value)
    {
        return $this->setFieldValue('trashed_on', $value);
    }

    /**
     * Return value of trashed_by_id field.
     *
     * @return int
     */
    public function getTrashedById()
    {
        return $this->getFieldValue('trashed_by_id');
    }

    /**
     * Set value of trashed_by_id field.
     *
     * @param  int $value
     * @return int
     */
    public function setTrashedById($value)
    {
        return $this->setFieldValue('trashed_by_id', $value);
    }

    /**
     * Return value of repeat_frequency field.
     *
     * @return string
     */
    public function getRepeatFrequency()
    {
        return $this->getFieldValue('repeat_frequency');
    }

    /**
     * Set value of repeat_frequency field.
     *
     * @param  string $value
     * @return string
     */
    public function setRepeatFrequency($value)
    {
        return $this->setFieldValue('repeat_frequency', $value);
    }

    /**
     * Return value of repeat_amount field.
     *
     * @return int
     */
    public function getRepeatAmount()
    {
        return $this->getFieldValue('repeat_amount');
    }

    /**
     * Set value of repeat_amount field.
     *
     * @param  int $value
     * @return int
     */
    public function setRepeatAmount($value)
    {
        return $this->setFieldValue('repeat_amount', $value);
    }

    /**
     * Return value of repeat_amount_extended field.
     *
     * @return int
     */
    public function getRepeatAmountExtended()
    {
        return $this->getFieldValue('repeat_amount_extended');
    }

    /**
     * Set value of repeat_amount_extended field.
     *
     * @param  int $value
     * @return int
     */
    public function setRepeatAmountExtended($value)
    {
        return $this->setFieldValue('repeat_amount_extended', $value);
    }

    /**
     * Return value of triggered_number field.
     *
     * @return int
     */
    public function getTriggeredNumber()
    {
        return $this->getFieldValue('triggered_number');
    }

    /**
     * Set value of triggered_number field.
     *
     * @param  int $value
     * @return int
     */
    public function setTriggeredNumber($value)
    {
        return $this->setFieldValue('triggered_number', $value);
    }

    /**
     * Return value of last_trigger_on field.
     *
     * @return DateValue
     */
    public function getLastTriggerOn()
    {
        return $this->getFieldValue('last_trigger_on');
    }

    /**
     * Set value of last_trigger_on field.
     *
     * @param  DateValue $value
     * @return DateValue
     */
    public function setLastTriggerOn($value)
    {
        return $this->setFieldValue('last_trigger_on', $value);
    }

    /**
     * Return value of fake_assignee_name field.
     *
     * @return string
     */
    public function getFakeAssigneeName()
    {
        return $this->getFieldValue('fake_assignee_name');
    }

    /**
     * Set value of fake_assignee_name field.
     *
     * @param  string $value
     * @return string
     */
    public function setFakeAssigneeName($value)
    {
        return $this->setFieldValue('fake_assignee_name', $value);
    }

    /**
     * Return value of fake_assignee_email field.
     *
     * @return string
     */
    public function getFakeAssigneeEmail()
    {
        return $this->getFieldValue('fake_assignee_email');
    }

    /**
     * Set value of fake_assignee_email field.
     *
     * @param  string $value
     * @return string
     */
    public function setFakeAssigneeEmail($value)
    {
        return $this->setFieldValue('fake_assignee_email', $value);
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
                case 'project_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'task_list_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'assignee_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'delegated_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'name':
                    return parent::setFieldValue($name, (string) $value);
                case 'body':
                    return parent::setFieldValue($name, (string) $value);
                case 'is_important':
                    return parent::setFieldValue($name, (bool) $value);
                case 'created_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'created_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'created_by_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'created_by_email':
                    return parent::setFieldValue($name, (string) $value);
                case 'updated_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'updated_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'updated_by_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'updated_by_email':
                    return parent::setFieldValue($name, (string) $value);
                case 'start_in':
                    return parent::setFieldValue($name, (int) $value);
                case 'due_in':
                    return parent::setFieldValue($name, (int) $value);
                case 'job_type_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'estimate':
                    return parent::setFieldValue($name, (float) $value);
                case 'position':
                    return parent::setFieldValue($name, (int) $value);
                case 'is_hidden_from_clients':
                    return parent::setFieldValue($name, (bool) $value);
                case 'is_trashed':
                    return parent::setFieldValue($name, (bool) $value);
                case 'original_is_trashed':
                    return parent::setFieldValue($name, (bool) $value);
                case 'trashed_on':
                    return parent::setFieldValue($name, datetimeval($value));
                case 'trashed_by_id':
                    return parent::setFieldValue($name, (int) $value);
                case 'repeat_frequency':
                    return parent::setFieldValue($name, (empty($value) ? null : (string) $value));
                case 'repeat_amount':
                    return parent::setFieldValue($name, (int) $value);
                case 'repeat_amount_extended':
                    return parent::setFieldValue($name, (int) $value);
                case 'triggered_number':
                    return parent::setFieldValue($name, (int) $value);
                case 'last_trigger_on':
                    return parent::setFieldValue($name, dateval($value));
                case 'fake_assignee_name':
                    return parent::setFieldValue($name, (string) $value);
                case 'fake_assignee_email':
                    return parent::setFieldValue($name, (string) $value);
                case 'raw_additional_properties':
                    return parent::setFieldValue($name, (string) $value);
            }

            throw new InvalidParamError('name', $name, "Field $name does not exist in this table");
        }
    }
}
