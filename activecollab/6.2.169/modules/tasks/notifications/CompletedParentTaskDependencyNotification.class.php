<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Completed parent task dependency notification.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage notifications
 */
class CompletedParentTaskDependencyNotification extends Notification
{
    /**
     * {@inheritdoc}
     */
    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        if ($recipient instanceof User) {
            $parent = $this->getParent();

            if (!$parent->isAssignee($recipient)) {
                return false;
            }

            if (
                !($channel instanceof WebInterfaceNotificationChannel) &&
                !($channel instanceof EmailNotificationChannel)
            ) {
                return false;
            }
        }

        return parent::isThisNotificationVisibleInChannel($channel, $recipient);
    }

    /**
     * {@inheritdoc}
     */
    public function isUserBlockingThisNotification(IUser $user, NotificationChannel $channel = null)
    {
        if ($user instanceof User && $channel instanceof EmailNotificationChannel) {
            $parent = $this->getParent();

            // Override notification blocking if recipient is assignee and has notifications_user_send_email_assignments set to true
            if ($parent instanceof Task && $parent->isAssignee($user) && !ConfigOptions::getValueFor('notifications_user_send_email_assignments', $user)) {
                return true;
            }
        }

        return parent::isUserBlockingThisNotification($user, $channel);
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        $result = parent::getAdditionalTemplateVars($channel);

        if ($channel instanceof EmailNotificationChannel) {
            if ($task = $this->getParent()) {
                $parents = $task->getParentDependencies();

                foreach ($parents as $key => $parent) {
                    if (($parent instanceof IComplete && $parent->isCompleted()) || ($this->getRecipients()[0]->isClient() && $parent->getIsHiddenFromClients())) {
                        unset($parents[$key]);
                    }
                }

                $result = array_merge($result, [
                    'completed_parent' => $this->getCompletedParent(),
                    'parents' => $parents,
                ]);
            }
        }

        return $result;
    }

    /**
     * Set completed parent task.
     *
     * @param Task $parent
     */
    public function &setCompletedParent(Task $parent)
    {
        $this->setAdditionalProperty('completed_parent_id', $parent->getId());

        return $this;
    }

    /**
     * Get completed parent task.
     *
     * @return Task
     */
    private function getCompletedParent()
    {
        return DataObjectPool::get(Task::class, $this->getAdditionalProperty('completed_parent_id'));
    }

    /**
     * Set update flags for combined object updates collection.
     *
     * @param array $updates
     */
    public function onObjectUpdateFlags(array &$updates)
    {
        if (empty($updates['parent_task_completed'])) {
            $updates['parent_task_completed'] = 1;
        } else {
            ++$updates['parent_task_completed'];
        }
    }
}
