<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Notifications;

use Angie;
use Angie\Error;
use Angie\Mailer\Decorator\Decorator;
use AngieApplication;
use AngieFramework;
use ApplicationObject;
use ClassNotImplementedError;
use Exception;
use FileDnxError;
use InvalidInstanceError;
use InvalidParamError;
use IUser;
use Notification;
use NotificationChannel;
use RealTimeNotificationChannel;
use ReflectionClass;
use SystemModule;
use WebInterfaceNotificationChannel;

class Notifications implements NotificationsInterface
{
    /**
     * Create a notification about given event within a given context.
     *
     * @param  string            $event
     * @param  ApplicationObject $context
     * @param  IUser             $sender
     * @param  Decorator         $decorator
     * @return Notification
     */
    public function notifyAbout($event, $context = null, $sender = null, $decorator = null)
    {
        $notification = $this->eventToNotificationInstance($event);

        if ($context) {
            $notification->setParent($context);
        }

        if ($sender) {
            $notification->setSender($sender);
        }

        if ($decorator instanceof Decorator) {
            $notification->setDecorator($decorator);
        }

        return $notification;
    }

    /**
     * Return notification template path.
     *
     * @param  Notification               $notification
     * @param  NotificationChannel|string $channel
     * @return string
     */
    public function getNotificationTemplatePath(Notification $notification, $channel)
    {
        $notification_class = get_class($notification);
        $channel_name = $channel instanceof NotificationChannel ? $channel->getShortName() : $channel;

        return AngieApplication::cache()->get(['notification_template_paths', $notification_class, $channel_name], function () use ($notification, $notification_class, $channel_name) {
            $class = new ReflectionClass($notification_class);

            $main_path = dirname($class->getFileName()) . "/{$channel_name}/" . $notification->getShortName() . '.tpl';

            if (is_file($main_path)) {
                return $main_path;
            } else {
                $parent_class = $class->getParentClass();

                $inherited_path = dirname($parent_class->getFileName()) . "/{$channel_name}/" . $notification->getShortName() . '.tpl';

                if (is_file($inherited_path)) {
                    return $inherited_path;
                } else {
                    throw new FileDnxError($main_path);
                }
            }
        });
    }

    /**
     * Convert event signature to class name, load the class and create an instance.
     *
     * @param  string       $event
     * @return Notification
     */
    private function eventToNotificationInstance($event)
    {
        if (strpos($event, '/') === false) {
            $module_name = SystemModule::NAME;
            $event_name = $event;
        } else {
            [$module_name, $event_name] = explode('/', $event);
        }

        $module = AngieApplication::getModule($module_name);

        if ($module instanceof AngieFramework) {
            $notification_class_name = Angie\Inflector::camelize($event_name) . 'Notification';
            $notification_class_path = $module->getPath() . "/notifications/{$notification_class_name}.class.php";

            if (!class_exists($notification_class_name, false)) {
                if (is_file($notification_class_path)) {
                    require_once $notification_class_path;

                    if (!class_exists($notification_class_name, false)) {
                        throw new ClassNotImplementedError($notification_class_name, $notification_class_path);
                    }
                } else {
                    throw new FileDnxError($notification_class_path, "Failed to load notification class for '$event' event");
                }
            }

            $notification = new $notification_class_name();

            if ($notification instanceof Notification) {
                return $notification;
            } else {
                throw new ClassNotImplementedError($notification_class_name, $notification_class_path, "Class '$notification_class_name' found, but it does not inherit Notification class");
            }
        } else {
            throw new InvalidParamError('event', $event, "Invalid module name found in '$event' event");
        }
    }

    // ---------------------------------------------------
    //  Channels and Sending
    // ---------------------------------------------------

    /**
     * Send $notification to the list of recipients.
     *
     * @param Notification $notification
     * @param IUser[]      $users
     * @param bool         $skip_sending_queue
     */
    public function sendNotificationToRecipients(Notification &$notification, $users, $skip_sending_queue = false)
    {
        if ($users instanceof IUser) {
            $users = [$users];
        }

        if (empty($users) || !is_foreachable($users)) {
            return;
        }

        if ($notification->isNew()) {
            $notification->save();
        }

        $recipients = [];

        // Check recipients list
        foreach ($users as $user) {
            if ($user instanceof IUser) {
                if (isset($recipients[$user->getEmail()])) {
                    continue;
                }

                if (!$notification->isThisNotificationVisibleToUser($user) || $notification->isUserBlockingThisNotification($user)) {
                    continue; // Remove from list of recipients if user can't see this notification, or if user is blocking it
                }

                $recipients[$user->getEmail()] = $user;
            } else {
                throw new InvalidInstanceError('user', $user, IUser::class);
            }
        }

        if (count($recipients)) {
            try {
                $this->openChannels();

                foreach ($recipients as $recipient) {
                    foreach ($this->getChannels() as $channel) {
                        if ($notification->isThisNotificationVisibleInChannel($channel, $recipient)) {
                            $channel->send($notification, $recipient, $skip_sending_queue);
                        }
                    }
                }

                $this->closeChannels();
            } catch (Exception $e) {
                $this->closeChannels(true);
                throw $e;
            }
        }
    }

    /**
     * Array of registered notification channels.
     *
     * @var NotificationChannel[]
     */
    private $channels = false;

    /**
     * Return notification channels.
     *
     * @return NotificationChannel[]
     */
    public function &getChannels()
    {
        if ($this->channels === false) {
            $this->channels = [
                new WebInterfaceNotificationChannel(),
                new RealTimeNotificationChannel(),
            ];

            Angie\Events::trigger('on_notification_channels', [&$this->channels]);
        }

        return $this->channels;
    }

    /**
     * Indicate whether channels are open.
     *
     * @var bool
     */
    private $channels_are_open = false;

    /**
     * Returns true if channels are open.
     *
     * @return bool
     */
    public function channelsAreOpen()
    {
        return $this->channels_are_open;
    }

    /**
     * Open notifications channels for bulk sending.
     */
    public function openChannels()
    {
        if ($this->channels_are_open) {
            throw new Error('Channels are already open');
        }

        foreach ($this->getChannels() as $channel) {
            $channel->open();
        }

        $this->channels_are_open = true;
    }

    /**
     * Close notification channels for bulk sending.
     *
     * @param bool $sending_interupted
     */
    public function closeChannels($sending_interupted = false)
    {
        if (empty($this->channels_are_open) && empty($sending_interupted)) {
            throw new Error('Channels are not open');
        }

        for ($i = count($this->channels) - 1; $i >= 0; --$i) {
            $this->channels[$i]->close($sending_interupted);
        }

        $this->channels_are_open = false;
    }
}
