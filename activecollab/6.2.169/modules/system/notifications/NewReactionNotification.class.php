<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class NewReactionNotification extends Notification
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'reaction_id' => $this->getReactionId(),
                'comment_id' => $this->getCommentId(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getMentionsFromParent()
    {
        return false;
    }

    public function getReactionId()
    {
        return $this->getAdditionalProperty('reaction_id');
    }

    public function getReactionType()
    {
        return $this->getAdditionalProperty('reaction_type');
    }

    public function getCommentId()
    {
        return $this->getAdditionalProperty('comment_id');
    }

    public function getReaction()
    {
        return DataObjectPool::get(Reaction::class, $this->getReactionId());
    }

    public function &setReaction(Reaction $reaction)
    {
        $this->setAdditionalProperty('reaction_id', $reaction->getId());
        $this->setAdditionalProperty('reaction_type', get_class($reaction));

        return $this;
    }

    public function &setComment(Comment $comment)
    {
        $this->setAdditionalProperty('comment_id', $comment->getId());

        return $this;
    }

    public function onObjectReactionFlags(array &$reactions)
    {
        $does_exist = false;

        $i = 0;
        foreach($reactions as $reaction) {
            if ($this->getReactionType() === $reaction['type']) {
                $reactions[$i]['react_count'] = (int) ($reaction['react_count'] + 1);
                $does_exist = true;
                break;
            }
            ++$i;
        }

        if (!$does_exist) {
            $reactions[] = [
                'type' => $this->getReactionType(),
                'react_count' => 1,
            ];
        }
    }

    public function isThisNotificationVisibleInChannel(NotificationChannel $channel, IUser $recipient)
    {
        return $channel instanceof WebInterfaceNotificationChannel;
    }

    public function onRelatedObjectsTypeIdsMap(array &$type_ids_map)
    {
        if (empty($type_ids_map[Comment::class])) {
            $type_ids_map[Comment::class] = [];
        }

        $type_ids_map[Comment::class][] = $this->getCommentId();

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

    public function isUserMentioned($user)
    {
        return false;
    }
}
