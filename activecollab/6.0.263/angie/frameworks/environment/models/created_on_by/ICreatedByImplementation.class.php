<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Created by implementation.
 *
 * @package angie.framework.environment
 * @subpackage models
 */
trait ICreatedByImplementation
{
    /**
     * Say hello to the paret object.
     */
    public function ICreatedByImplementation()
    {
        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['created_by_id'] = $this->getCreatedById();
            $result['created_by_name'] = $this->getCreatedByName();
            $result['created_by_email'] = $this->getCreatedByEmail();
        });

        $this->registerEventHandler('on_before_save', function ($is_new) {
            if ($is_new && $this->getCreatedById() == 0 && $this->getCreatedByName() == '' && $this->getCreatedByEmail() == '') {
                if (AngieApplication::isAuthenticationLoaded()) {
                    $this->setCreatedBy(AngieApplication::authentication()->getLoggedUser());
                }
            }
        });
    }

    /**
     * Set created by instance.
     *
     * @return IUser
     */
    public function getCreatedBy()
    {
        $created_by = $this->getCreatedById() ? Users::findById($this->getCreatedById()) : null;

        if ($created_by instanceof User) {
            return $created_by;
        } elseif ($this->getCreatedByEmail()) {
            return new AnonymousUser($this->getCreatedByName(), $this->getCreatedByEmail());
        }

        return null;
    }

    /**
     * Set instance of user who created parent object.
     *
     * @param User|IUser|null $created_by
     */
    public function setCreatedBy($created_by)
    {
        if ($created_by === null) {
            $this->setCreatedById(0);
            $this->setCreatedByName('');
            $this->setCreatedByEmail('');
        } elseif ($created_by instanceof User) {
            $this->setCreatedById($created_by->getId());
            $this->setCreatedByName($created_by->getDisplayName());
            $this->setCreatedByEmail($created_by->getEmail());
        } elseif ($created_by instanceof AnonymousUser) {
            $this->setCreatedById(0);
            $this->setCreatedByName($created_by->getName());
            $this->setCreatedByEmail($created_by->getEmail());
        }
    }

    /**
     * Return true if $user is author of this object.
     *
     * @param  IUser $user
     * @return bool
     */
    public function isCreatedBy(IUser $user)
    {
        if ($this->getCreatedById()) {
            return $this->getCreatedById() == $user->getId();
        } else {
            return $this->getCreatedById() == 0 && $this->getCreatedByEmail() == $user->getEmail();
        }
    }

    // ---------------------------------------------------
    //  Expectatons
    // ---------------------------------------------------

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Return ID of user who created this object.
     *
     * @return int
     */
    abstract public function getCreatedById();

    /**
     * Set ID of user who created this object.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setCreatedById($value);

    /**
     * Return name of user who created this object.
     *
     * @return string
     */
    abstract public function getCreatedByName();

    /**
     * Set name of user who created this object.
     *
     * @param  string $value
     * @return string
     */
    abstract public function setCreatedByName($value);

    /**
     * Return email of user who created this object.
     *
     * @return string
     */
    abstract public function getCreatedByEmail();

    /**
     * Set email of user who created this object.
     *
     * @param  string $value
     * @return string
     */
    abstract public function setCreatedByEmail($value);
}
