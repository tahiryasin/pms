<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * ActiveCollab Timer integration.
 *
 * @package ActiveCollab.modules.tracking
 * @subpackage model
 */
class TimerIntegration extends Integration
{
    /**
     * Returns true if this integration is singleton (can be only one integration of this type in the system).
     *
     * @return bool
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * Returns true if this integration is in use.
     *
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ActiveCollab Timer';
    }

    /**
     * Return integration short name.
     *
     * @return string
     */
    public function getShortName()
    {
        return 'timer';
    }

    /**
     * Return short integration description.
     *
     * @return string
     */
    public function getDescription()
    {
        return lang('Track time spent on tasks');
    }

    /**
     * Get group of this integration.
     *
     * @return string
     */
    public function getGroup()
    {
        return 'applications';
    }

    // ---------------------------------------------------
    //  Settings
    // ---------------------------------------------------

    /**
     * @param  int $value
     * @return int
     */
    public function setMinimalTimeEntry($value)
    {
        return $this->setAdditionalProperty('minimal_time_entry', (int) $value);
    }

    /**
     * @param  int $value
     * @return int
     */
    public function setRoundingInterval($value)
    {
        return $this->setAdditionalProperty('rounding_interval', (int) $value);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'minimal_time_entry' => $this->getMinimalTimeEntry(),
            'rounding_interval' => $this->getRoundingInterval(),
        ]);
    }

    /**
     * @return int
     */
    public function getMinimalTimeEntry()
    {
        return $this->getAdditionalProperty('minimal_time_entry', 15);
    }

    /**
     * @return int
     */
    public function getRoundingInterval()
    {
        return $this->getAdditionalProperty('rounding_interval', 15);
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * All members can access Timer settings.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return !($user instanceof Client);
    }
}
