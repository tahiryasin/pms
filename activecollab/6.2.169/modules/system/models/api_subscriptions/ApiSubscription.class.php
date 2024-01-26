<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Authentication\AuthenticatedUser\RepositoryInterface;

/**
 * ApiSubscription class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class ApiSubscription extends BaseApiSubscription
{
    /**
     * Return name.
     *
     * @return string
     */
    public function getName()
    {
        return lang('API Subscription #:num', ['num' => $this->getId()]);
    }

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
     * Return parent user account.
     *
     * @return User
     */
    public function getUser()
    {
        return DataObjectPool::get(User::class, $this->getUserId());
    }

    /**
     * Set user.
     *
     * @param  User                 $user
     * @return User
     * @throws InvalidInstanceError
     */
    public function setUser(User $user)
    {
        if ($user instanceof User) {
            $this->setUserId($user->getId());
        } else {
            throw new InvalidInstanceError('user', $user, User::class);
        }

        return $user;
    }

    /**
     * Return API URL.
     *
     * @return string
     */
    public function getApiUrl()
    {
        return ROOT_URL . '/api/v1';
    }

    /**
     * Return formatted token.
     *
     * @return string
     */
    public function getFormattedToken()
    {
        return $this->getUserId() . '-' . $this->getTokenId();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'user_id' => $this->getUserId(),
            'client_name' => $this->getClientName(),
            'client_vendor' => $this->getClientVendor(),
            'token' => $this->getFormattedToken(),
            'last_used_on' => $this->getLastUsedOn(),
            'requests_count' => $this->getRequestsCount(),
        ]);
    }

    // ---------------------------------------------------
    //  Routing context
    // ---------------------------------------------------

    public function getRoutingContext(): string
    {
        return 'api_subscription';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'api_subscription_id' => $this->getId(),
        ];
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can update this API client subscription.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $this->getUser() instanceof User ? $this->getUser()->canEdit($user) : false;
    }

    /**
     * Returns true if $user can delete this API client subscription.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $this->getUser() instanceof User ? $this->getUser()->canEdit($user) : false;
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if (!$this->validatePresenceOf('client_name')) {
            $errors->addError('Client name is required', 'client_name');
        }

        if (!$this->validatePresenceOf('user_id')) {
            $errors->addError('User ID is required', 'user_id');
        }

        if ($this->validatePresenceOf('token_id') && strlen($this->getTokenId()) == 40) {
            if (!$this->validateUniquenessOf('token_id')) {
                $errors->addError('Subscription token needs to be unique', 'token_id');
            }
        } else {
            $errors->addError('Subscription token is required', 'token_id');
        }
    }

    /**
     * Save to database.
     */
    public function save()
    {
        if (!$this->getTokenId()) {
            $this->setTokenId(ApiSubscriptions::generateToken());
        }

        parent::save();
    }
}
