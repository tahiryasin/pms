<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Updated by implementation.
 *
 * @package angie.framework.environment
 * @subpackage models
 */
trait IUpdatedByImplementation
{
    /**
     * Say hello to the paret object.
     */
    public function IUpdatedByImplementation()
    {
        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['updated_by_id'] = $this->getUpdatedById();
        });

        $this->registerEventHandler('on_before_save', function ($is_new, $modifications) {
            $this->autoSetUpdatedBy($is_new);
        });
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
     * Return ID of user who updated this object.
     *
     * @return int
     */
    abstract public function getUpdatedById();

    /**
     * Automatically set author if that value is not set already.
     *
     * @param bool $is_new
     */
    private function autoSetUpdatedBy($is_new)
    {
        if ($is_new && $this->getUpdatedById() == 0 && $this->getUpdatedByName() == '' && $this->getUpdatedByEmail() == '') {
            $this->setUpdatedBy(AngieApplication::authentication()->getLoggedUser());
        } elseif (empty($is_new)) {
            $this->setUpdatedBy(AngieApplication::authentication()->getLoggedUser());
        }
    }

    // ---------------------------------------------------
    //  Expectatons
    // ---------------------------------------------------

    /**
     * Return name of user who updated this object.
     *
     * @return string
     */
    abstract public function getUpdatedByName();

    /**
     * Return email of user who updated this object.
     *
     * @return string
     */
    abstract public function getUpdatedByEmail();

    /**
     * Set instance of user who updated parent object.
     *
     * @param User|IUser|null $updated_by
     */
    public function setUpdatedBy($updated_by)
    {
        if ($updated_by === null) {
            $this->setUpdatedById(0);
            $this->setUpdatedByName('');
            $this->setUpdatedByEmail('');
        } elseif ($updated_by instanceof User) {
            $this->setUpdatedById($updated_by->getId());
            $this->setUpdatedByName($updated_by->getDisplayName());
            $this->setUpdatedByEmail($updated_by->getEmail());
        } elseif ($updated_by instanceof AnonymousUser) {
            $this->setUpdatedById(0);
            $this->setUpdatedByName($updated_by->getName());
            $this->setUpdatedByEmail($updated_by->getEmail());
        }
    }

    /**
     * Set ID of user who updated this object.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setUpdatedById($value);

    /**
     * Set name of user who updated this object.
     *
     * @param  string $value
     * @return string
     */
    abstract public function setUpdatedByName($value);

    /**
     * Set email of user who updated this object.
     *
     * @param  string $value
     * @return string
     */
    abstract public function setUpdatedByEmail($value);

    /**
     * Set updated by instance.
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        $updated_by = DataObjectPool::get('User', $this->getUpdatedById());

        if ($updated_by instanceof User) {
            return $updated_by;
        } elseif ($this->getUpdatedByEmail()) {
            return new AnonymousUser($this->getUpdatedByName(), $this->getUpdatedByEmail());
        }

        return null;
    }
}
