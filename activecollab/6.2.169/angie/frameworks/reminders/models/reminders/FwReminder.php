<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

/**
 * Framework level reminder implementation.
 *
 * @package angie.frameworks.reminders
 * @subpackage models
 */
abstract class FwReminder extends BaseReminder implements RoutingContextInterface
{
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
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'comment' => $this->getComment(),
            'send_on' => $this->getSendOn(),
            'subscribers' => $this->getSubscribersAsArray(), // This is recipients list, and it needs to be included in general reminder JSON
        ]);
    }

    /**
     * Send a reminder.
     */
    abstract public function send();

    /**
     * @return bool
     */
    public function canView(User $user)
    {
        return $this->isCreatedBy($user);
    }

    /**
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $this->isCreatedBy($user);
    }

    /**
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $this->isCreatedBy($user);
    }

    public function getRoutingContext(): string
    {
        return 'reminder';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'reminder_id' => $this->getId(),
        ];
    }

    public function touchParentOnPropertyChange(): ?array
    {
        return [
            'parent_type',
            'parent_id',
        ];
    }

    /**
     * Validate before save.
     */
    public function validate(ValidationErrors &$errors)
    {
        $this->validatePresenceOf('send_on') or $errors->addError('Reminder time is required', 'send_on');

        parent::validate($errors);
    }
}
