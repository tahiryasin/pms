<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Authentication\AuthenticatedUser\RepositoryInterface;

/**
 * UserSession class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class UserSession extends BaseUserSession
{
    /**
     * {@inheritdoc}
     */
    public function getAuthenticatedUser(RepositoryInterface $repository)
    {
        $user = DataObjectPool::get(User::class, $this->getUserId());

        if (!$user instanceof User) {
            throw new LogicException('User associated with the session is not found');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function extendSession($reference_timestamp = null)
    {
        $reference = $reference_timestamp === null ? DateTimeValue::now() : DateTimeValue::makeFromTimestamp($reference_timestamp);

        $this->setLastUsedOn($reference);
        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'user_id' => $this->getUserId(),
            'last_used_on' => $this->getLastUsedOn(),
            'requests_count' => $this->getRequestsCount(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ValidationErrors &$errors)
    {
        $this->validatePresenceOf('user_id', 1);
        $this->validatePresenceOf('session_id');
        $this->validatePresenceOf('session_ttl');
        $this->validatePresenceOf('csrf_validator');
    }

    /**
     * Save to database.
     */
    public function save()
    {
        if (!$this->getSessionId()) {
            $this->setSessionId(UserSessions::generateSessionId());
        }

        if (!$this->getCsrfValidator()) {
            $this->setCsrfValidator(UserSessions::generateCsrfValidator());
        }

        if (!$this->getLastUsedOn()) {
            $this->setLastUsedOn(DateTimeValue::now());
        }

        parent::save();
    }
}
