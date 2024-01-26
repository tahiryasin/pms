<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level custom reminder implementation.
 *
 * @package angie.frameworks.reminders
 * @subpackage models
 */
abstract class FwCustomReminder extends Reminder
{
    /**
     * List of user ids from attribute subscribers.
     *
     * @var array
     */
    private $subscriber_ids;

    /**
     * Override default set attribute method.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function setAttribute($attribute, $value)
    {
        if ($attribute == 'subscribers') {
            $this->subscriber_ids = $value;
        }

        parent::setAttribute($attribute, $value);
    }

    /**
     * Save record to the database.
     */
    public function save()
    {
        parent::save();

        if (!is_array($this->subscriber_ids) || !in_array($this->getCreatedBy()->getId(), $this->subscriber_ids)) {
            $this->unsubscribe($this->getCreatedBy());
        }
    }

    /**
     * Send custom reminder notification.
     */
    public function send()
    {
        if ($subscribers = $this->getSubscribers()) {
            $parent = $this->getParent();

            if ($parent instanceof ApplicationObject && $parent->isAccessible()) {
                AngieApplication::notifications()->notifyAbout(RemindersFramework::INJECT_INTO . '/custom_reminder', $parent)
                    ->setReminder($this)
                    ->sendToUsers($subscribers, true);
            }
        }
    }
}
