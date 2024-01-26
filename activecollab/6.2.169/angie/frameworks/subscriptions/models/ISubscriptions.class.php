<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Interface that all objects that have subscribers need to implement.
 *
 * @package angie.frameworks.subscriptions
 * @subpackage models
 */
interface ISubscriptions
{
    /**
     * Returns true if this object has people subscribed to it.
     *
     * @return bool
     */
    public function hasSubscribers();

    /**
     * Return number of people who are subscribed to this object.
     *
     * @return int
     */
    public function countSubscribers();

    /**
     * Returns subscribers as simple array.
     */
    public function getSubscribersAsArray();

    /**
     * Return array of subscribed users.
     *
     * @return IUser[]|User[]
     */
    public function getSubscribers();

    /**
     * Set array of subscribers.
     *
     * @param  array $users
     * @param  bool  $replace
     * @param  bool  $touch
     * @return array
     */
    public function setSubscribers($users, $replace = true, $touch = true);

    /**
     * Unsubscribe everyone.
     */
    public function clearSubscribers();

    /**
     * Return ID-s of subscribers.
     *
     * @return array
     */
    public function getSubscriberIds();

    /**
     * Return subscription code for the given user.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getSubscriptionCodeFor(IUser $user);

    /**
     * Check if $user is subscribed to this object.
     *
     * @param  IUser             $user
     * @param  bool              $use_cache
     * @return bool
     * @throws InvalidParamError
     */
    public function isSubscribed(IUser $user, $use_cache = true);

    /**
     * Subscribe $user to this object.
     *
     * @param IUser $user
     * @param bool  $bulk
     */
    public function subscribe(IUser $user, $bulk = false);

    /**
     * Unsubscribe $user from this object.
     *
     * @param IUser $user
     * @param bool  $bulk
     */
    public function unsubscribe(IUser $user, $bulk = false);

    /**
     * Clone this object's subscriptions to a different object.
     *
     * @param ISubscriptions $to
     * @param array          $limit_user_ids
     */
    public function cloneSubscribersTo(ISubscriptions $to, $limit_user_ids = []);

    /**
     * Returns true if $user can subscribe to this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canSubscribe(User $user);

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return true if $user can view the parent object.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user);

    /**
     * Return true if $user can update the parent object.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user);
}
