<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class CrispNotificationForExistingUser implements CrispNotificationInterface
{
    private $user;

    const SLUG = 'for-existing-user';

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getState()
    {
        return ConfigOptions::getValueFor(
            self::LIVE_CHAT_NOTIFICATION_FOR_EXISTING_USERS,
            $this->user
        );
    }

    private function setState($state)
    {
        ConfigOptions::setValueFor(
            self::LIVE_CHAT_NOTIFICATION_FOR_EXISTING_USERS,
            $this->user,
            $state
        );

        return $this->getState();
    }

    private function getNextState()
    {
        switch ($this->getState()) {
            case CrispNotificationInterface::NOTIFICATION_STATUS_DISABLED:
                return CrispNotificationInterface::NOTIFICATION_STATUS_ENABLED;
            case self::NOTIFICATION_STATUS_ENABLED:
                return CrispNotificationInterface::NOTIFICATION_STATUS_DISMISSED;
            default:
                return CrispNotificationInterface::NOTIFICATION_STATUS_DISMISSED;
        }
    }

    public function enable()
    {
        if ($this->getNextState() === CrispNotificationInterface::NOTIFICATION_STATUS_ENABLED) {
            return $this->setState($this->getNextState());
        }

        throw new LogicException(get_class($this) . " can not be enabled from the current state '{$this->getState()}'");
    }

    public function dismiss()
    {
        if ($this->getNextState() === CrispNotificationInterface::NOTIFICATION_STATUS_DISMISSED) {
            return $this->setState($this->getNextState());
        }

        throw new LogicException(get_class($this) . " can not be dismissed from the current state '{$this->getState()}'");
    }
}
