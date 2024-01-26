<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;
use Angie\Globalization;

/**
 * Time record instance class.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage models
 */
class TimeRecord extends BaseTimeRecord implements RoutingContextInterface
{
    /**
     * Construct data object and if $id is present load.
     *
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->addHistoryFields('job_type_id');
    }

    /**
     * Return true if parent is optional.
     *
     * @return bool
     */
    public function isParentOptional()
    {
        return false;
    }

    /**
     * Return proper type name in user's language.
     *
     * @param  bool     $lowercase
     * @param  Language $language
     * @return string
     */
    public function getVerboseType($lowercase = false, $language = null)
    {
        return $lowercase ? lang('time record', null, true, $language) : lang('Time Record', null, true, $language);
    }

    /**
     * Set job type for a given time record.
     *
     * @param  JobType              $job_type
     * @throws InvalidInstanceError
     */
    public function setJobType(JobType $job_type)
    {
        if ($job_type instanceof JobType) {
            $this->setJobTypeId($job_type->getId());
        } else {
            throw new InvalidInstanceError('job_type', $job_type, 'JobType');
        }
    }

    /**
     * Return name string.
     *
     * @param  bool   $with_value
     * @return string
     */
    public function getName($with_value = false)
    {
        $user = $this->getUser();
        $value = $this->getValue();

        if ($with_value) {
            $value_job = $this->getJobType() instanceof JobType ? $this->getFormatedValue($value * $this->getJobType()->getHourlyRateFor($this->getProject())) : 0;

            return $value == 1 ?
                lang(':value hour of :job (:costs)', ['value' => $value, 'job' => $this->getJobTypeName(), 'costs' => $value_job]) :
                lang(':value hours of :job (:costs)', ['value' => $value, 'job' => $this->getJobTypeName(), 'costs' => $value_job]);
        } else {
            if ($user instanceof User) {
                return $value == 1 ?
                    lang(':value hour of :job by :name', ['value' => $value, 'job' => $this->getJobTypeName(), 'name' => $user->getDisplayName(true)]) :
                    lang(':value hours of :job by :name', ['value' => $value, 'job' => $this->getJobTypeName(), 'name' => $user->getDisplayName(true)]);
            } else {
                return $value == 1 ?
                    lang(':value hour of :job', ['value' => $value, 'job' => $this->getJobTypeName()]) :
                    lang(':value hours of :job', ['value' => $value, 'job' => $this->getJobTypeName()]);
            }
        }
    }

    /**
     * Return time record job type.
     *
     * @return JobType
     */
    public function getJobType()
    {
        return DataObjectPool::get('JobType', $this->getJobTypeId());
    }

    /**
     * Return value formated with currency.
     *
     * @param  float  $value
     * @return string
     */
    public function getFormatedValue($value)
    {
        return Globalization::formatNumber($value);
    }

    /**
     * Return name of the job type.
     *
     * @return string
     */
    public function getJobTypeName()
    {
        return $this->getJobType() instanceof JobType ? $this->getJobType()->getName() : JobTypes::getNameById($this->getJobTypeId());
    }

    /**
     * Return Currency.
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->getProject() instanceof Project && $this->getProject()->getCurrency() instanceof Currency ? $this->getProject()->getCurrency() : null;
    }

    /**
     * Convert time to money.
     *
     * @return float
     */
    public function calculateExpense()
    {
        return $this->getValue() * $this->getJobType()->getHourlyRateFor($this->getProject());
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();
        $result['job_type_id'] = $this->getJobTypeId();
        $result['user_name'] = $this->getUserName();
        $result['user_email'] = $this->getUserEmail();
        $result['source'] = $this->getSource();

        return $result;
    }

    // ---------------------------------------------------
    //  Interface implementations
    // ---------------------------------------------------

    public function getRoutingContext(): string
    {
        return 'time_record';
    }

    public function getRoutingContextParams(): array
    {
        $parent = $this->getParent();

        if ($parent instanceof Task) {
            $project = $parent->getProject();
        } else {
            $project = $parent;
        }

        return [
            'project_id' => $project->getId(),
            'time_record_id' => $this->getId(),
        ];
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Return true if $user can delete this record.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $this->canEdit($user);
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

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
        if ($name === 'value') {
            if (strpos($value, ':') !== false) {
                $value = time_to_float($value);
            }

            if ($value < 0.01) {
                $value = 0.01;
            }
        }

        return parent::setFieldValue($name, $value);
    }

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if ($this->validatePresenceOf('job_type_id')) {
            if ($this->isNew()) {
                if ($job_type = $this->getJobType()) {
                    if ($job_type->getIsArchived()) {
                        $errors->addError('Archived job types cannot be used for new time records', 'job_type_id');
                    }
                } else {
                    $errors->fieldValueIsRequired('job_type_id');
                }
            }
        } else {
            $errors->fieldValueIsRequired('job_type_id');
        }

        parent::validate($errors);
    }
}
