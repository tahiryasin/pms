<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Object's subscriptions helper implementation.
 *
 * @package angie.frameworks.subscriptions
 * @subpackage models
 */
trait ISubscriptionsImplementation
{
    /**
     * List of users that should be subscribed when object is saved.
     *
     * @var array
     */
    private $subscribe_on_save = [];

    /**
     * Say hello to the parent object.
     */
    public function ISubscriptionsImplementation()
    {
        $this->registerEventHandler('on_describe_single', function (array &$result) {
            $result['subscribers'] = $this->getSubscribersAsArray();
        });

        $this->registerEventHandler('on_set_attribute', function ($attribute, $value) {
            if ($attribute == 'subscribers' && is_array($value)) {
                foreach ($value as $subscriber) {
                    if (is_numeric($subscriber)) {
                        $this->subscribe_on_save[] = $subscriber;
                    } else {
                        if (is_string($subscriber) && is_valid_email($subscriber)) {
                            $this->subscribe_on_save[] = new AnonymousUser(null, $subscriber);
                        } else {
                            if (is_array($subscriber) && count($subscriber) && is_valid_email($subscriber[1])) {
                                $this->subscribe_on_save[] = new AnonymousUser($subscriber[0], $subscriber[1]);
                            }
                        }
                    }
                }
            }
        });

        $this->registerEventHandler('on_after_save', function ($was_new) {
            if ($was_new) {
                if (empty($this->subscribe_on_save)) {
                    $this->subscribe_on_save = [];
                }

                if ($this instanceof ICreatedBy && $this->getCreatedById()) {
                    $this->subscribe_on_save[] = $this->getCreatedById(); // Subscribe author
                }

                if ($this instanceof IAssignees && $this->getAssigneeId()) {
                    $this->subscribe_on_save[] = $this->getAssigneeId(); // Subscribe assignee
                }

                if (count($this->subscribe_on_save)) {
                    $this->setSubscribers($this->subscribe_on_save, false, false);
                }

                $this->subscribe_on_save = [];
            } else {
                if ($this instanceof IHiddenFromClients && $this->getIsHiddenFromClients()) {
                    $this->unsubscribeClientsAndRemoveNotifications();
                }
            }
        });

        $this->registerEventHandler('on_before_delete', function () {
            Subscriptions::deleteByParent($this);
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
     * Returns subscribers as simple array.
     *
     * @return array
     */
    public function getSubscribersAsArray()
    {
        $result = [];

        if ($subscribers = DB::execute('SELECT user_id, user_name, user_email FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this))) {
            foreach ($subscribers as $subscriber) {
                if ($subscriber['user_id']) {
                    $result[] = $subscriber['user_id'];
                } else {
                    $result[] = [(string) $subscriber['user_name'], (string) $subscriber['user_email']];
                }
            }
        }

        return $result;
    }

    /**
     * Set array of subscribers.
     *
     * @param  array     $users
     * @param  bool      $replace
     * @param  bool      $touch
     * @return array
     * @throws Exception
     */
    public function setSubscribers($users, $replace = true, $touch = true)
    {
        DB::transact(function () use ($users, $replace, $touch) {
            $to_subscribe = [];

            if ($replace) {
                Subscriptions::deleteByParent($this); // cleanup
            } else {
                $already_subscribed = DB::executeFirstColumn("SELECT LOWER(user_email) AS 'user_email' FROM subscriptions WHERE " . Subscriptions::parentToCondition($this));
            }

            if (empty($already_subscribed)) {
                $already_subscribed = [];
            }

            if ($users && is_foreachable($users)) {
                $load_user_details = [];

                foreach ($users as $user) {
                    // We have user instance
                    if ($user instanceof IUser) {
                        $user_email = $user->getEmail();

                        if (empty($to_subscribe[$user_email])) {
                            $to_subscribe[$user_email] = [$user->getId(), $user->getDisplayName(), $user_email];
                        }

                        // Email address
                    } else {
                        if ($user && is_valid_email($user)) {
                            if (empty($to_subscribe[$user])) {
                                $to_subscribe[$user] = [0, $user, $user];
                            }

                            // [ User Name, user@email.com ]
                        } else {
                            if (is_array($user) && count($user) == 2 && is_valid_email($user[1])) {
                                if (empty($to_subscribe[$user[1]])) {
                                    $to_subscribe[$user[1]] = [0, $user[0], $user[1]];
                                }

                                // User ID? Load it later, with a single query
                            } else {
                                if (is_numeric($user)) {
                                    $load_user_details[] = (int) $user;
                                }
                            }
                        }
                    }
                }

                $this->loadUserDetailsToSubscribe($to_subscribe, $load_user_details, $already_subscribed);

                if (empty($replace)) {
                    $this->filterOutAlreadySubscribed($to_subscribe, $already_subscribed);
                }

                $this->insertSubscribers($to_subscribe);
            }

            if ($touch) {
                $this->touch();
            }
        }, 'Setting object subscribers');

        return $this->getSubscribersAsArray();
    }

    /**
     * Load user details and subscribe existing users.
     *
     * @param array $to_subscribe
     * @param array $load_user_details
     * @param array $already_subscribed
     */
    private function loadUserDetailsToSubscribe(&$to_subscribe, $load_user_details, $already_subscribed)
    {
        if (empty($already_subscribed)) {
            $already_subscribed = [];
        }

        if (count($load_user_details)) {
            if ($rows = DB::execute("SELECT id, first_name, last_name, LOWER(email) AS 'email' FROM users WHERE id IN (?)", $load_user_details)) {
                foreach ($rows as $row) {
                    $user_email = $row['email'];

                    if (empty($to_subscribe[$user_email]) && !in_array($user_email, $already_subscribed)) {
                        $to_subscribe[$user_email] = [$row['id'], Users::getUserDisplayName($row), $user_email];
                    }
                }
            }
        }
    }

    /**
     * Additional filtering to make sure that email address is not already subscribed.
     *
     * @param array $to_subscribe
     * @param array $already_subscribed
     */
    private function filterOutAlreadySubscribed(&$to_subscribe, $already_subscribed)
    {
        if (count($to_subscribe) && count($already_subscribed)) {
            $unfiltered = $to_subscribe;

            $to_subscribe = [];
            foreach ($unfiltered as $email_address => $subscriber_data) {
                if (!in_array($email_address, $already_subscribed)) {
                    $to_subscribe[$email_address] = $subscriber_data;
                }
            }
        }
    }

    /**
     * Insert subscribers to the database.
     *
     * @param array $to_subscribe
     */
    private function insertSubscribers(&$to_subscribe)
    {
        if (count($to_subscribe)) {
            $batch = new DBBatchInsert('subscriptions', ['user_id', 'user_name', 'user_email', 'parent_type', 'parent_id', 'subscribed_on', 'code']);

            $parent_type = DB::escape(get_class($this));
            $parent_id = DB::escape($this->getId());
            $now = DB::escape(DateTimeValue::now());

            foreach ($to_subscribe as $record) {
                $batch->insertEscapedArray([DB::escape($record[0]), DB::escape($record[1]), DB::escape($record[2]), $parent_type, $parent_id, $now, DB::escape($this->prepareSubscriptionCode())]);
            }

            $batch->done();
        }
    }

    /**
     * Return object ID.
     */
    abstract public function getId();

    /**
     * Prepare a new subscription code.
     *
     * @return string
     */
    private function prepareSubscriptionCode()
    {
        return make_string(10);
    }

    /**
     * Refresh object's updated_on flag.
     *
     * @param User|null  $by
     * @param array|null $additional
     * @param bool       $save
     */
    abstract public function touch($by = null, $additional = null, $save = true);

    /**
     * Returns true if this object has people subscribed to it.
     *
     * @return bool
     */
    public function hasSubscribers()
    {
        return (bool) $this->countSubscribers();
    }

    /**
     * Return number of people who are subscribed to this object.
     *
     * @return int
     */
    public function countSubscribers()
    {
        return DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this));
    }

    /**
     * Return array of subscribed users.
     *
     * @return AnonymousUser[]|User[]
     */
    public function getSubscribers()
    {
        $result = [];

        if ($subscribers = Users::findFlattenFromUserListingTable('subscriptions', 'user', Subscriptions::parentToCondition($this), STATE_TRASHED)) {
            foreach ($subscribers as $subscriber) {
                if (($subscriber instanceof ITrash && $subscriber->getIsTrashed()) || ($subscriber instanceof IArchive && $subscriber->getIsArchived())) {
                    continue; // Clean up trashed users. If we don't do it like this, trashed users would be added as anonymous users
                }

                $result[] = $subscriber;
            }
        }

        return count($result) ? $result : null;
    }

    /**
     * Clear subscriptions.
     */
    public function clearSubscribers()
    {
        if ($this->countSubscribers()) {
            DB::execute('DELETE FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this));
            $this->touch();
        }
    }

    /**
     * Filter subscribers.
     *
     * @param array $limit_user_ids
     * @param bool  $drop_anonymous_subscriptions
     */
    public function filterSubscribers($limit_user_ids = [], $drop_anonymous_subscriptions = true)
    {
        $parent_conditions = Subscriptions::parentToCondition($this);

        if ($drop_anonymous_subscriptions) {
            DB::execute("DELETE FROM subscriptions WHERE $parent_conditions AND user_id NOT IN (?)", $limit_user_ids);
        } else {
            DB::execute("DELETE FROM subscriptions WHERE $parent_conditions AND user_id > '0' AND user_id NOT IN (?)", $limit_user_ids);
        }

        if (DB::affectedRows()) {
            $this->touch();
        }
    }

    /**
     * Return ID-s of subscribers.
     *
     * @return array
     */
    public function getSubscriberIds()
    {
        return AngieApplication::cache()->getByObject($this, ['subscriptions', 'ids'], function () {
            if ($rows = DB::execute('SELECT users.id FROM users, subscriptions WHERE ' . Subscriptions::parentToCondition($this) . ' AND users.id = subscriptions.user_id')) {
                $result = [];

                foreach ($rows as $row) {
                    $result[] = (int) $row['id'];
                }

                return $result;
            }

            return null;
        });
    }

    /**
     * Return subscription code for the given user.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getSubscriptionCodeFor(IUser $user)
    {
        return AngieApplication::cache()->getByObject($this, ['subscriptions', 'codes', $user->getEmail()], function () use ($user) {
            if ($subscription = DB::executeFirstRow('SELECT id, code FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this) . ' AND user_email = ?', $user->getEmail())) {
                return 'SUBS-' . $subscription['id'] . '-' . $subscription['code'];
            } else {
                return null;
            }
        });
    }

    /**
     * Subscribe $user from this object.
     *
     * @param IUser $user
     * @param bool  $bulk
     */
    public function subscribe(IUser $user, $bulk = false)
    {
        if (!$this->isSubscribed($user, false)) {
            DB::execute('INSERT INTO subscriptions (parent_type, parent_id, user_id, user_name, user_email, subscribed_on, code) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP(), ?)', get_class($this), $this->getId(), $user->getId(), $user->getDisplayName(), $user->getEmail(), $this->prepareSubscriptionCode());

            if (!$bulk) {
                $this->touch();
            }
        }
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Check if $user is subscribed to this object.
     *
     * @param  IUser                $user
     * @param  bool                 $use_cache
     * @return bool
     * @throws InvalidInstanceError
     */
    public function isSubscribed(IUser $user, $use_cache = true)
    {
        if ($user instanceof User && $user->isNew()) {
            return false;
        }

        if ($user instanceof IUser) {
            return AngieApplication::cache()->getByObject($this, ['subscriptions', $user->getEmail()], function () use ($user) {
                if ($user instanceof User) {
                    return (bool) DB::executeFirstCell("SELECT COUNT(id) AS 'row_count' FROM subscriptions WHERE " . Subscriptions::parentToCondition($this) . ' AND (user_id = ? OR user_email = ?)', $user->getId(), $user->getEmail());
                } else {
                    return (bool) DB::executeFirstCell("SELECT COUNT(id) AS 'row_count' FROM subscriptions WHERE " . Subscriptions::parentToCondition($this) . ' AND user_email = ?', $user->getEmail());
                }
            }, !$use_cache);
        } else {
            throw new InvalidInstanceError('user', $user, 'IUser');
        }
    }

    // ---------------------------------------------------
    //  Expecations
    // ---------------------------------------------------

    /**
     * Unsubscribe $user from this object.
     *
     * @param IUser $user
     * @param bool  $bulk
     */
    public function unsubscribe(IUser $user, $bulk = false)
    {
        if ($this->isSubscribed($user, false)) {
            if ($user instanceof User) {
                DB::execute('DELETE FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this) . ' AND (user_id = ? OR user_email = ?)', $user->getId(), $user->getEmail());
            } elseif ($user instanceof AnonymousUser) {
                DB::execute('DELETE FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this) . ' AND user_email = ?', $user->getEmail());
            }

            if (empty($bulk)) {
                $this->touch();
            }
        }
    }

    /**
     * Clone this object's subscriptions to a different object.
     *
     * @param DataObject|ISubscriptions $to
     * @param array                     $limit_user_ids
     */
    public function cloneSubscribersTo(ISubscriptions $to, $limit_user_ids = [])
    {
        if (empty($limit_user_ids)) {
            return;
        }

        $rows = DB::execute(
            'SELECT user_id, user_name, user_email FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this)
        );

        if ($rows) {
            $batch = new DBBatchInsert(
                'subscriptions',
                [
                    'parent_type',
                    'parent_id',
                    'user_id',
                    'user_name',
                    'user_email',
                    'subscribed_on',
                    'code',
                ],
                50,
                DBBatchInsert::REPLACE_RECORDS
            );

            $parent_type = DB::escape(get_class($to));
            $parent_id = DB::escape($to->getId());
            $now = DB::escape(DateTimeValue::now());

            try {
                DB::beginWork('Begin: cloning subscriptions @ ' . __CLASS__);

                foreach ($rows as $row) {
                    if ($row['user_id'] && !in_array($row['user_id'], $limit_user_ids)) {
                        continue;
                    }

                    $batch->insertEscapedArray(
                        [
                            $parent_type,
                            $parent_id,
                            DB::escape($row['user_id']),
                            DB::escape($row['user_name']),
                            DB::escape($row['user_email']),
                            $now,
                            DB::escape($this->prepareSubscriptionCode()),
                        ]
                    );
                }

                $batch->done();

                DB::commit('Done: cloning subscriptions @ ' . __CLASS__);
            } catch (Exception $e) {
                DB::rollback('Rollback: cloning subscriptions @ ' . __CLASS__);
                throw $e;
            }
        }
    }

    /**
     * Returns true if $user can subscribe to this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canSubscribe(User $user)
    {
        return $this->isAccessible() && $this->canView($user);
    }

    /**
     * Return true if this object is accessible.
     *
     * @return bool
     */
    abstract public function isAccessible();

    /**
     * Return true if $user can view parent object.
     *
     * @param  User $user
     * @return bool
     */
    abstract public function canView(User $user);

    /**
     * Unsubscribe clients and remove notifications.
     */
    private function unsubscribeClientsAndRemoveNotifications()
    {
        if ($this->hasSubscribers()) {
            $subscribers = $this->getSubscribers();

            if (!empty($subscribers)) {
                $clients = array_filter($subscribers, function ($subscriber) {
                    return $subscriber instanceof Client;
                });

                if (!empty($clients)) {
                    $client_ids = array_map(function (Client $client) {
                        return $client->getId();
                    }, $clients);

                    DB::execute('DELETE FROM subscriptions WHERE ' . Subscriptions::parentToCondition($this) . ' AND user_id IN (?)', $client_ids);

                    if ($notification_ids = DB::executeFirstColumn('SELECT id FROM notifications WHERE parent_type = ? AND parent_id = ?', get_class($this), $this->getId())) {
                        DB::execute('DELETE FROM notification_recipients WHERE notification_id IN (?) AND recipient_id IN (?)', $notification_ids, $client_ids);

                        foreach ($clients as $client) {
                            AngieApplication::cache()->removeByObject($client, Notifications::READ_CACHE_KEY);
                        }
                    }
                }
            }
        }
    }
}
