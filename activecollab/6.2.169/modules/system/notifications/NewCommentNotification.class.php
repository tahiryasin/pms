<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level new comment notification.
 *
 * @package ActiveCollab.modules.system
 * @subpackage notifications
 */
class NewCommentNotification extends Notification
{
    /**
     * Serialize to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), ['comment_id' => $this->getCommentId()]);
    }

    /**
     * In case of new comment, collect mentions from the comment, not the parent.
     *
     * @return bool
     */
    protected function getMentionsFromParent()
    {
        return false;
    }

    /**
     * Return comment ID.
     *
     * @return int
     */
    public function getCommentId()
    {
        return $this->getAdditionalProperty('comment_id');
    }

    /**
     * Return parent comment.
     *
     * @return Comment
     */
    public function getComment()
    {
        return DataObjectPool::get(Comment::class, $this->getCommentId());
    }

    /**
     * Set a parent comment.
     *
     * @param  Comment                $comment
     * @return NewCommentNotification
     */
    public function &setComment(Comment $comment)
    {
        $this->setAdditionalProperty('comment_id', $comment->getId());

        if (is_foreachable($comment->getNewMentions())) {
            $this->setMentionedUsers($comment->getNewMentions());
        }

        return $this;
    }

    /**
     * Return additional template variables.
     *
     * @param  NotificationChannel $channel
     * @return array
     */
    public function getAdditionalTemplateVars(NotificationChannel $channel)
    {
        $result = ['comment' => $this->getComment()];

        if ($channel instanceof EmailNotificationChannel) {
            $parent = $this->getParent();

            $result['total_comments'] = $parent instanceof IComments ? $parent->countComments() : 0;
            $result['latest_comments'] = $parent instanceof IComments ? $parent->getLatestComments(5) : null;
        }

        return $result;
    }

    /**
     * Set update flags for combined object updates collection.
     *
     * @param array $updates
     */
    public function onObjectUpdateFlags(array &$updates)
    {
        if (empty($updates['new_comments'])) {
            $updates['new_comments'] = 1;
        } else {
            ++$updates['new_comments'];
        }
    }

    /**
     * This method is called when we need to load related notification objects for API response.
     *
     * @param array $type_ids_map
     */
    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
        if (empty($type_ids_map[Comment::class])) {
            $type_ids_map[Comment::class] = [];
        }

        if (!in_array($this->getCommentId(), $type_ids_map[Comment::class])) {
            $type_ids_map[Comment::class][] = $this->getCommentId();
        }

        $parent = $this->getParent();

        if ($parent instanceof IProjectElement) {
            if (empty($type_ids_map[Project::class])) {
                $type_ids_map[Project::class] = [$parent->getProjectId()];
            } else {
                if (!in_array($parent->getProjectId(), $type_ids_map[Project::class])) {
                    $type_ids_map[Project::class][] = $parent->getProjectId();
                }
            }
        }
    }

    public function optOutConfigurationOptions(NotificationChannel $channel = null)
    {
        if ($channel instanceof EmailNotificationChannel) {
            return array_merge(parent::optOutConfigurationOptions($channel), ['notifications_user_send_email_subscriptions']);
        }

        return parent::optOutConfigurationOptions($channel);
    }
}
