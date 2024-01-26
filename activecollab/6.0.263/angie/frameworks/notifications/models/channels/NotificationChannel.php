<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Base notification channel.
 *
 * @package angie.frameworks.notifications
 * @subpackage models
 */
abstract class NotificationChannel
{
    /**
     * Cached short name.
     *
     * @var bool
     */
    private $short_name = false;

    /**
     * Return short name.
     *
     * @return string
     */
    public function getShortName()
    {
        if ($this->short_name === false) {
            $class_name = get_class($this);

            $this->short_name = Angie\Inflector::underscore(substr($class_name, 0, strlen($class_name) - 19));
        }

        return $this->short_name;
    }

    /**
     * Return verbose name of the channel.
     *
     * @return string
     */
    abstract public function getVerboseName();

    // ---------------------------------------------------
    //  Enable / Disable / Settings
    // ---------------------------------------------------

    /**
     * Returns true if this channel is enabled by default.
     *
     * @return bool
     */
    public function isEnabledByDefault()
    {
        return $this->canOverrideDefaultStatus() ? ConfigOptions::getValue($this->getShortName() . '_notifications_enabled') : true;
    }

    /**
     * Set enabled by default.
     *
     * @param  bool                $value
     * @throws NotImplementedError
     */
    public function setEnabledByDefault($value)
    {
        if ($this->canOverrideDefaultStatus()) {
            ConfigOptions::setValue($this->getShortName() . '_notifications_enabled', (bool) $value);
        } else {
            throw new NotImplementedError(__METHOD__);
        }
    }

    /**
     * Returns true if this channel is enabled for this user.
     *
     * @param  User $user
     * @return bool
     */
    public function isEnabledFor(User $user)
    {
        if ($this->canOverrideDefaultStatus()) {
            return ConfigOptions::getValueFor($this->getShortName() . '_notifications_enabled', $user);
        } else {
            return $this->isEnabledByDefault();
        }
    }

    /**
     * Set enabled for given user.
     *
     * @param  User              $user
     * @param  bool|null         $value
     * @throws InvalidParamError
     */
    public function setEnabledFor(User $user, $value)
    {
        if ($value === true || $value === false) {
            ConfigOptions::setValueFor($this->getShortName() . '_notifications_enabled', $user, $value);
        } elseif ($value === null) {
            ConfigOptions::removeValuesFor($user, $this->getShortName() . '_notifications_enabled');
        } else {
            throw new InvalidParamError('value', $value, '$value can be BOOL value or NULL');
        }
    }

    /**
     * Returns true if $user can override default enable / disable status.
     *
     * @return bool
     */
    public function canOverrideDefaultStatus()
    {
        return ConfigOptions::exists($this->getShortName() . '_notifications_enabled');
    }

    // ---------------------------------------------------
    //  Open / Close
    // ---------------------------------------------------

    /**
     * Open channel for sending.
     */
    public function open()
    {
    }

    /**
     * Close channel after notifications have been sent.
     *
     * @param bool $sending_interupted
     */
    public function close($sending_interupted = false)
    {
    }

    /**
     * Send notification via this channel.
     *
     * @param Notification $notification
     * @param IUser        $recipient
     * @param bool         $skip_sending_queue
     */
    abstract public function send(Notification &$notification, IUser $recipient, $skip_sending_queue = false);
}
