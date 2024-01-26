<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Assignees interface implementation.
 *
 * @package angie.frameworks.assignees
 * @subpackage models
 */
trait IAssigneesImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function IAssigneesImplementation()
    {
        $this->registerEventHandler(
            'on_json_serialize',
            function (array &$result) {
                $result['assignee_id'] = $this->getAssigneeId();
                $result['delegated_by_id'] = $this->getDelegatedById();
            }
        );

        $this->registerEventHandler(
            'on_set_attributes',
            function (&$attributes) {
                if (array_key_exists('assignee_id', $attributes)
                    && ($this->isNew() || $this->getAssigneeId() != $attributes['assignee_id'])
                ) {
                    /** @var User $assignee */
                    $assignee = $attributes['assignee_id'] ? Users::findById($attributes['assignee_id']) : null;

                    $this->setAssignee(
                        $assignee,
                        AngieApplication::authentication()->getAuthenticatedUser(),
                        false
                    );
                }

                if (isset($attributes['assignee_id'])) {
                    unset($attributes['assignee_id']); // Unset!
                }
            }
        );

        $this->registerEventHandler(
            'on_before_save',
            function () {
                if ($this->getAssigneeId() && !$this->getDelegatedById()) {
                    $this->setDelegatedById($this instanceof ICreatedBy && $this->getCreatedById()
                        ? $this->getCreatedById()
                        : AngieApplication::authentication()->getLoggedUserId());
                }
            }
        );

        $this->registerEventHandler(
            'on_history_field_renderers',
            function (&$renderers) {
                $renderers['assignee_id'] = function ($old_value, $new_value, Language $language) {
                    $ids = [];

                    if ($old_value) {
                        $ids[] = $old_value;
                    }

                    if ($new_value) {
                        $ids[] = $new_value;
                    }

                    $names = Users::getIdNameMap($ids);

                    if ($old_value && $new_value) {
                        return lang(
                            'Reassigned from <b>:old_assignee</b> to <b>:new_assignee</b>',
                            [
                                'old_assignee' => $names[$old_value] ?? lang('Deleted user'),
                                'new_assignee' => $names[$new_value] ?? lang('Deleted user'),
                            ],
                            true,
                            $language
                        );
                    } elseif ($new_value) {
                        return lang(
                            '<b>:new_assignee</b> is responsible for this :object_type',
                            [
                                'new_assignee' => $names[$new_value] ?? lang('Deleted user'),
                                'object_type' => $this->getVerboseType(true, $language),
                            ],
                            true,
                            $language
                        );
                    } elseif ($old_value) {
                        return lang(
                            '<b>:old_assignee</b> is no longer responsible for this :object_type',
                            [
                                'old_assignee' => $names[$old_value] ?? lang('Deleted user'),
                                'object_type' => $this->getVerboseType(true, $language),
                            ],
                            true,
                            $language
                        );
                    } else {
                        return null;
                    }
                };
            }
        );
    }

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Return value of assignee_id field.
     *
     * @return int
     */
    abstract public function getAssigneeId();

    /**
     * Return value of delegated_by_id field.
     *
     * @return int
     */
    abstract public function getDelegatedById();

    /**
     * Return true if this object is not saved to database.
     *
     * @return bool
     */
    abstract public function isNew();

    /**
     * Set assignee.
     *
     * @param User|null $assignee
     * @param mixed     $delegated_by
     * @param bool      $save
     */
    public function setAssignee($assignee, $delegated_by = null, $save = true)
    {
        if ($assignee instanceof User) {
            $this->setAssigneeId($assignee->getId());
            $this->setDelegatedBy($delegated_by);
        } elseif ($assignee === null) {
            $this->setAssigneeId(0);
            $this->setDelegatedBy(null);
        }

        if ($save) {
            $this->save();
        }
    }

    /**
     * Set value of assignee_id field.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setAssigneeId($value);

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Set user who delegated this instance.
     *
     * @param  User                 $user
     * @return User
     * @throws InvalidInstanceError
     */
    public function setDelegatedBy($user)
    {
        if ($user instanceof User) {
            $this->setDelegatedById($user->getId());
        } elseif ($user === null) {
            $this->setDelegatedById(0);
        } else {
            throw new InvalidInstanceError(
                'user',
                $user,
                User::class,
                '$user can be User instance, or NULL'
            );
        }

        return $user;
    }

    /**
     * Set value of delegated_by_id field.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setDelegatedById($value);

    /**
     * Save to database.
     */
    abstract public function save();

    /**
     * Return proper type name in user's language.
     *
     * @param  bool     $lowercase
     * @param  Language $language
     * @return string
     */
    abstract public function getVerboseType($lowercase = false, $language = null);

    /**
     * Returns true if $user is assigned to this object.
     *
     * @param  User $user
     * @return bool
     */
    public function isAssignee(User $user)
    {
        if ($user instanceof User && !$user->getIsTrashed()) {
            return $this->getAssigneeId() && $this->getAssigneeId() == $user->getId();
        } else {
            return false;
        }
    }

    /**
     * Returns true if this object has assignee set.
     *
     * @return bool
     */
    public function hasAssignee()
    {
        return $this->getAssignee() instanceof User;
    }

    /**
     * Return assignee instance.
     *
     * @return User|null
     */
    public function getAssignee()
    {
        $assignee = DataObjectPool::get(User::class, $this->getAssigneeId());

        return $assignee instanceof User && !$assignee->getIsTrashed() ? $assignee : null;
    }

    /**
     * Return user who delegated this assignment to assignees.
     *
     * @return User
     */
    public function getDelegatedBy()
    {
        return DataObjectPool::get(User::class, $this->getDelegatedById());
    }

    /**
     * Return object ID.
     *
     * @return int
     */
    abstract public function getId();

    /**
     * Refresh object's updated_on flag.
     *
     * @param User|null  $by
     * @param array|null $additional
     * @param bool       $save
     */
    abstract public function touch($by = null, $additional = null, $save = true);
}
