<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;
use ActiveCollab\Foundation\Urls\Router\RouterInterface;
use Angie\Mailer\Decorator\Decorator;

/**
 * Notification class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class Notification extends BaseNotification implements RoutingContextInterface
{
    /**
     * Cached short name.
     *
     * @var string
     */
    private $short_name = false;

    /**
     * Serialize to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['sender_id'] = $this->getSenderId();

        if (empty($result['sender_id'])) {
            $sender = $this->getSender();

            if ($sender instanceof AnonymousUser) {
                $result['sender_name'] = $sender->getName();
                $result['sender_email'] = $sender->getEmail();
            } else {
                $result['sender_name'] = lang('Unknown');
                $result['sender_email'] = 'noreply@activecollab.com';
            }
        }

        return $result;
    }

    /**
     * Return short name.
     *
     * @return string
     */
    public function getShortName()
    {
        if ($this->short_name === false) {
            $class_name = get_class($this);

            $this->short_name = Angie\Inflector::underscore(substr($class_name, 0, strlen($class_name) - 12));
        }

        return $this->short_name;
    }

    /**
     * Return template path for a given channel.
     *
     * @param  NotificationChannel|string $channel
     * @return string
     * @throws FileDnxError
     */
    public function getTemplatePath($channel)
    {
        return AngieApplication::notifications()->getNotificationTemplatePath($this, $channel);
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        return [];
    }

    /**
     * Return notification sender.
     *
     * @return IUser|null
     */
    public function getSender()
    {
        return $this->getUserFromFieldSet('sender');
    }

    /**
     * Set notification sender.
     *
     * @param  IUser|null $user
     * @return IUser|null
     */
    public function setSender($user)
    {
        return $this->setUserFromFieldSet($user, 'sender');
    }

    /**
     * Custom notification decorator.
     *
     * @var Decorator
     */
    private $decorator = false;

    /**
     * @param  Decorator $decorator
     * @return $this
     */
    public function &setDecorator(Decorator $decorator)
    {
        $this->decorator = $decorator;

        return $this;
    }

    /**
     * @return Decorator
     */
    public function getDecorator()
    {
        return $this->decorator instanceof Decorator ? $this->decorator : \Angie\Mailer::getDecorator();
    }

    /**
     * Returns true if $user is sender of this notification.
     *
     * @param  IUser                $user
     * @return bool
     * @throws InvalidInstanceError
     */
    public function isSender(IUser $user)
    {
        if ($user instanceof User) {
            return $this->getSenderId() == $user->getId();
        } elseif ($user instanceof IUser) {
            return strcasecmp($this->getSenderEmail(), $user->getEmail()) == 0;
        } else {
            throw new InvalidInstanceError('user', $user, 'IUser');
        }
    }

    /**
     * Return true if we should collect mentiones info from the parent (in cases where parent is a valid ApplicationObject instance).
     *
     * @return bool
     */
    protected function getMentionsFromParent()
    {
        return true;
    }

    /**
     * Set notification parent instance.
     *
     * @param  ApplicationObject    $parent
     * @param  bool                 $save
     * @return ApplicationObject
     * @throws InvalidInstanceError
     */
    public function setParent($parent, $save = false)
    {
        if ($parent instanceof IBody && $this->getMentionsFromParent() && is_foreachable($parent->getNewMentions())) {
            $this->setMentionedUsers($parent->getNewMentions());
        }

        return parent::setParent($parent, $save);
    }

    /**
     * Return files attached to this notification, if any.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAttachments(NotificationChannel $channel)
    {
        return [];
    }

    /**
     * Return visit URL.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getVisitUrl(IUser $user)
    {
        return $this->getParent() instanceof RoutingContextInterface ? $this->getParent()->getViewUrl() : '#';
    }

    /**
     * Return public unsubscribe URL.
     *
     * @param  IUser  $user
     * @return string
     */
    public function getUnsubscribeUrl(IUser $user)
    {
        $parent = $this->getParent();

        if ($parent instanceof ISubscriptions) {
            return AngieApplication::getContainer()
                ->get(RouterInterface::class)
                    ->assemble(
                        'public_notifications_unsubscribe',
                        [
                            'code' => $parent->getSubscriptionCodeFor($user),
                        ]
                    );
        }

        return '#';
    }

    public function getRoutingContext(): string
    {
        return 'notification';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'notification_id' => $this->getId(),
        ];
    }

    // ---------------------------------------------------
    //  Repipients
    // ---------------------------------------------------

    /**
     * Return true if $user is recipient of this notification.
     *
     * @TODO Add caching support?
     *
     * @param  IUser $user
     * @return bool
     */
    public function isRecipient(IUser $user)
    {
        if ($user instanceof User) {
            return (bool) DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM notification_recipients WHERE notification_id = ? AND recipient_id = ?', $this->getId(), $user->getId());
        } else {
            return (bool) DB::executeFirstCell('SELECT COUNT(id) AS "row_count" FROM notification_recipients WHERE notification_id = ? AND recipient_email = ?', $this->getId(), $user->getEmail());
        }
    }

    /**
     * Cached array of recipients.
     *
     * @var array
     */
    private $recipients = false;

    /**
     * Return array of notification recipients.
     *
     * @param  bool  $use_cache
     * @return array
     */
    public function getRecipients($use_cache = true)
    {
        if (empty($use_cache) || $this->recipients === false) {
            $this->recipients = Users::findOnlyUsersFromUserListingTable('notification_recipients', 'recipient', DB::prepare('notification_recipients.notification_id = ?', $this->getId()));
        }

        return $this->recipients;
    }

    /**
     * Add recipients to this notification.
     *
     * @param  User|User[]          $r
     * @throws InvalidParamError
     * @throws InvalidInstanceError
     */
    public function addRecipient($r)
    {
        if ($r instanceof User) {
            $r = [$r];
        }

        if (is_foreachable($r)) {
            $notification_id = $this->getId();
            $batch = new DBBatchInsert('notification_recipients', ['notification_id', 'recipient_id', 'recipient_name', 'recipient_email', 'is_mentioned']);

            foreach ($r as $recipient) {
                $batch->insert($notification_id, $recipient->getId(), $recipient->getName(), $recipient->getEmail(), $this->isUserMentioned($recipient));
            }

            $batch->done();
        } else {
            throw new InvalidParamError('r', $r, '$r is expected to be one or more IUser instances');
        }
    }

    /**
     * Remove one or more recipients from this notification.
     *
     * @param User|User[] $r
     */
    public function removeRecipient($r)
    {
        if ($r instanceof User) {
            $r = [$r];
        }

        if (is_foreachable($r)) {
            $recipient_ids = [];

            foreach ($r as $recipient) {
                $recipient_ids[] = $recipient->getId();
            }

            DB::execute('DELETE FROM notification_recipients WHERE notification_id = ? AND recipient_id IN (?)', $this->getId(), $recipient_ids);
        }
    }

    /**
     * Remove all recipients from this notification.
     */
    public function clearRecipients()
    {
        DB::execute('DELETE FROM notification_recipients WHERE notification_id = ?', $this->getId());
        $this->recipients = false;
    }

    /**
     * Return true if $user read this notification.
     *
     * @param  User $user
     * @param  bool $use_cache
     * @return bool
     */
    public function isRead(User $user, $use_cache = true)
    {
        return Notifications::isRead($this, $user, $use_cache);
    }

    // ---------------------------------------------------
    //  User groups
    // ---------------------------------------------------

    /**
     * Send to administrators.
     *
     * @param  bool         $skip_sending_queue
     * @return Notification
     */
    public function &sendToAdministrators($skip_sending_queue = false)
    {
        return $this->sendToUsers(Users::findOwners(), $skip_sending_queue);
    }

    /**
     * Notify notification to financial managers.
     *
     * @param  bool                $skip_sending_queue
     * @param  mixed               $exclude_user
     * @throws NotImplementedError
     */
    public function sendToFinancialManagers($skip_sending_queue = false, $exclude_user = null)
    {
        $notify_people = null;

        if ($this instanceof InvoicePaidNotification) {
            $notify_managers = ConfigOptions::getValue('invoice_notify_financial_managers'); //only for InvoicePaidNotification

            if ($notify_managers == Invoice::INVOICE_NOTIFY_FINANCIAL_MANAGERS_ALL) {
                $notify_people = Invoices::findFinancialManagers($exclude_user);
            } elseif ($notify_managers == Invoice::INVOICE_NOTIFY_FINANCIAL_MANAGERS_SELECTED) {
                $notify_manager_ids = ConfigOptions::getValue('invoice_notify_financial_manager_ids');
                if (is_foreachable($notify_manager_ids)) {
                    $notify_people = []; //check is user still financial manager

                    foreach ($notify_manager_ids as $user_id) {
                        $user = DataObjectPool::get('User', $user_id);
                        if ($user instanceof User && $user->isFinancialManager()) {
                            if ($exclude_user instanceof User && $exclude_user->getId() == $user->getId()) {
                                continue; // skip if user is exclude user
                            }

                            $notify_people[] = $user;
                        }
                    }
                }
            }
        } else {
            $notify_people = Invoices::findFinancialManagers();
        }

        if ($notify_people) {
            $this->sendToUsers($notify_people, $skip_sending_queue);
        }
    }

    /**
     * Send to subscribers.
     *
     * @param  bool                 $skip_sending_queue
     * @return Notification
     * @throws InvalidInstanceError if $context does not implement ISubscriptions interface
     */
    public function &sendToSubscribers($skip_sending_queue = false)
    {
        $parent = $this->getParent();

        if ($parent instanceof ISubscriptions) {
            if ($parent->hasSubscribers()) {
                return $this->sendToUsers($parent->getSubscribers(), $skip_sending_queue);
            } else {
                return $this;
            }
        } else {
            throw new InvalidInstanceError('parent', $parent, 'ISubscriptions');
        }
    }

    /**
     * Send to multiple groups of users.
     *
     * @param  array             $groups
     * @param  bool              $skip_sending_queue
     * @throws InvalidParamError
     */
    public function &sendToGroupsOfUsers($groups, $skip_sending_queue = false)
    {
        if (is_foreachable($groups)) {
            $users = [];

            foreach ($groups as $group) {
                if ($group && is_foreachable($group)) {
                    foreach ($group as $user) {
                        if ($user instanceof IUser) {
                            $email = $user->getEmail();

                            if (isset($users[$email])) {
                                continue;
                            }

                            $users[$email] = $user;
                        } else {
                            throw new InvalidParamError('groups', $groups, '$groups can have arrays of IUser instances only');
                        }
                    }
                }
            }

            $this->sendToUsers($users, $skip_sending_queue);
        } else {
            throw new InvalidParamError('groups', $groups, '$groups is expected to be array of groups of users');
        }
    }

    /**
     * Send to provided group of users.
     *
     * @param  IUser|IUser[]        $users
     * @param  bool                 $skip_sending_queue
     * @return Notification
     * @throws InvalidParamError
     * @throws InvalidInstanceError
     * @throws Exception
     */
    public function &sendToUsers($users, $skip_sending_queue = false)
    {
        AngieApplication::notifications()->sendNotificationToRecipients($this, $users, $skip_sending_queue);

        return $this;
    }

    // ---------------------------------------------------
    //  Utility Methods
    // ---------------------------------------------------

    /**
     * Returns true if $user was mentioned in this notification.
     *
     * @param  IUser $user
     * @return bool
     */
    public function isUserMentioned($user)
    {
        if ($user instanceof User) {
            $mentioned_users = $this->getMentionedUsers();

            return is_array($mentioned_users) && in_array($user->getId(), $mentioned_users);
        }

        return false;
    }

    /**
     * Return array of mentioned users, if any.
     *
     * @return array|null
     */
    public function getMentionedUsers()
    {
        return $this->getAdditionalProperty('mentioned_users');
    }

    /**
     * Set array of mentioned users.
     *
     * @param  array $value
     * @return array
     */
    protected function setMentionedUsers($value)
    {
        return $this->setAdditionalProperty('mentioned_users', $value);
    }

    /**
     * Return true if this notification is set for a newly created parent object.
     *
     * @return bool
     */
    protected function isNewlyCreated()
    {
        return str_starts_with(get_class($this), 'New');
    }

    /**
     * Returns true if $user can see this notification.
     *
     * @param  IUser $user
     * @return bool
     */
    public function isThisNotificationVisibleToUser(IUser $user)
    {
        if ($this->ignoreSender() && $this->isSender($user)) {
            return false; // Ignore sender by default
        }

        return true;
    }

    /**
     * Return true if sender should be ignored.
     *
     * @return bool
     */
    public function ignoreSender()
    {
        return true;
    }

    /**
     * Returns true if $user is blocking this notifcation.
     *
     * @param  IUser               $user
     * @param  NotificationChannel $channel
     * @return bool
     */
    public function isUserBlockingThisNotification(IUser $user, NotificationChannel $channel = null)
    {
        if ($user instanceof User) {
            // @ mention override for email channel when notifications_user_send_email_mentions is set to TRUE
            if ($channel instanceof EmailNotificationChannel && $this->isUserMentioned($user) && ConfigOptions::getValueFor('notifications_user_send_email_mentions', $user)) {
                return false;
            }

            foreach ($this->optOutConfigurationOptions($channel) as $option) {
                if (!ConfigOptions::getValueFor($option, $user)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns an array of options that users can set to opt out from this notification.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function optOutConfigurationOptions(NotificationChannel $channel = null)
    {
        return [];
    }

    /**
     * Return true if this notification supports Gmail go to action.
     *
     * @see    https://developers.google.com/gmail/markup/reference/go-to-action
     * @param  IUser $recipient
     * @return bool
     */
    public function supportsGoToAction(IUser $recipient)
    {
        return false;
    }

    /**
     * This notification should not be displayed in web interface.
     *
     * @param  NotificationChannel $channel
     * @param  IUser               $recipient
     * @return bool
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($recipient instanceof User) {
            if ($channel->isEnabledFor($recipient)) {
                // @ mention override opt out options when notifications_user_send_email_mentions is set to TRUE
                if ($channel instanceof EmailNotificationChannel && $this->isUserMentioned($recipient) && ConfigOptions::getValueFor('notifications_user_send_email_mentions', $recipient)) {
                    return true;
                }

                foreach ($this->optOutConfigurationOptions($channel) as $option) {
                    if (!ConfigOptions::getValueFor($option, $recipient)) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Set update flags for combined object updates collection.
     *
     * @param array $updates
     */
    public function onObjectUpdateFlags(array &$updates)
    {
    }

    /**
     * This method is called when we need to load related notification objects for API response.
     *
     * @param array $type_ids_map
     */
    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
    }

    /**
     * Set reaction flags for combined object updates collection.
     *
     * @param array $reactions
     */
    public function onObjectReactionFlags(array &$reactions)
    {
    }

    // ---------------------------------------------------
    //  Template variables
    // ---------------------------------------------------

    /**
     * Array of additional template vars, indexed by variable name.
     *
     * @var array
     */
    private $additional_template_vars = [];

    /**
     * Set additional template variables.
     *
     * @param  mixed        $p1
     * @param  mixed        $p2
     * @return Notification
     */
    public function &setAdditionalTemplateVars($p1, $p2 = null)
    {
        if (is_array($p1)) {
            $this->additional_template_vars = array_merge($this->additional_template_vars, $p1);
        } else {
            $this->additional_template_vars[$p1] = $p2;
        }

        return $this;
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Delete notification from the database.
     *
     * @param  bool      $bulk
     * @throws Exception
     */
    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Deleting notification @ ' . __CLASS__);

            DB::execute('DELETE FROM notification_recipients WHERE notification_id = ?', $this->getId());
            parent::delete($bulk);

            DB::commit('Notification deleted @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to delete notification @ ' . __CLASS__);
            throw $e;
        }
    }
}
