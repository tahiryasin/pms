<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package angie.frameworks.environment
 * @subpackage models
 */
class PusherSocketPayloadTransformator extends WebhookPayloadTransformator
{
    /**
     * Event data payload limit for pusher is 10kb (https://pusher.com/docs/rest_api#method-post-event).
     */
    const PUSHER_PAYLOAD_LIMIT = 10240;

    /**
     * {@inheritdoc}
     */
    public function shouldTransform($url)
    {
        return strpos($url, 'api.pusherapp.com') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($event_type, DataObject $payload)
    {
        if (!in_array($event_type, $this->getSupportedEvents())) {
            return null;
        }

        $transformator = $event_type . 'PayloadTransformator';

        if (method_exists(self::class, $transformator)) {
            return $this->$transformator($payload);
        } else {
            throw new Exception("Transformator method {$transformator} not implemented");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedEvents()
    {
        return [
            'CommentCreated',
            'ReactionCreated',
            'ReactionDeleted',
            'TaskCreated',
            'TaskUpdated',
            'TaskCompleted',
            'TaskReopened',
            'TaskListChanged',
            'TaskMoveToTrash',
            'TaskRestoredFromTrash',
            'TaskReordered',
            'TaskListCreated',
            'TaskListUpdated',
            'TaskListReordered',
            'TaskListMoveToTrash',
            'TaskListRestoredFromTrash',
            'TaskListCompleted',
            'TaskListReopened',
            'StopwatchCreated',
            'StopwatchUpdated',
            'StopwatchDeleted',
        ];
    }

    public function StopwatchCreatedPayloadTransformator(Stopwatch $stopwatch)
    {
        return $stopwatch->jsonSerialize();
    }

    public function StopwatchUpdatedPayloadTransformator(Stopwatch $stopwatch)
    {
        return $stopwatch->jsonSerialize();
    }

    public function StopwatchDeletedPayloadTransformator(Stopwatch $stopwatch)
    {
        return $stopwatch->jsonSerialize();
    }

    /**
     * Transform payload when comment is created.
     *
     * @param  Comment $comment
     * @return array
     */
    public function CommentCreatedPayloadTransformator(Comment $comment)
    {
        $data = [
            'id' => $comment->getId(),
            'parent_id' => $comment->getParentId(),
            'parent_type' => $comment->getParentType(),
            'body_formatted' => $comment->getFormattedBody(),
            'attachments' => $comment->getAttachments() ? $comment->getAttachments() : [],
            'created_by_id' => $comment->getCreatedById(),
            'created_on' => $comment->getCreatedOn(),
            'url' => $comment->getUrlPath(),
            'is_complete_data' => true,
        ];

        if ($this->calculateDataSize($data) >= self::PUSHER_PAYLOAD_LIMIT) {
            return [
                'id' => $comment->getId(),
                'url' => $comment->getUrlPath(),
                'is_complete_data' => false,
            ];
        } else {
            return $data;
        }
    }

    /**
     * Transform payload when reaction is created.
     *
     * @param  Reaction $reaction
     * @return array
     */
    public function ReactionCreatedPayloadTransformator(Reaction $reaction)
    {
        return $this->reactionPayload($reaction);
    }

    /**
     * Transform payload when reaction is deleted.
     *
     * @param  Reaction $reaction
     * @return array
     */
    public function ReactionDeletedPayloadTransformator(Reaction $reaction)
    {
        return $this->reactionPayload($reaction);
    }

    /**
     * Reaction payload.
     *
     * @param  Reaction $reaction
     * @return array
     */
    private function reactionPayload(Reaction $reaction)
    {
        return [
            'id' => $reaction->getId(),
            'class' => get_class($reaction),
            'parent_id' => $reaction->getParentId(),
            'parent_type' => $reaction->getParentType(),
            'created_by_id' => $reaction->getCreatedById(),
            'created_by_name' => $reaction->getCreatedByName(),
            'created_by_email' => $reaction->getCreatedByEmail(),
            'created_on' => $reaction->getCreatedOn(),
        ];
    }

    private function TaskCreatedPayloadTransformator(Task $task)
    {
        $data = array_merge(
            $task->jsonSerialize(),
            [
                'is_complete_data' => true,
            ]
        );

        if ($this->calculateDataSize($data) >= self::PUSHER_PAYLOAD_LIMIT) {
            return [
                'id' => $task->getId(),
                'project_id' => $task->getProjectId(),
                'url' => $task->getUrlPath(),
                'is_complete_data' => false,
            ];
        } else {
            return $data;
        }
    }

    private function TaskUpdatedPayloadTransformator(Task $task)
    {
        return $this->TaskCreatedPayloadTransformator($task);
    }

    private function TaskCompletedPayloadTransformator(Task $task)
    {
        return [
            'id' => $task->getId(),
            'project_id' => $task->getProjectId(),
            'is_completed' => $task->isCompleted(),
            'open_dependencies' => $task->getOpenDependencies(),
            'is_complete_data' => true,
        ];
    }

    private function TaskReopenedPayloadTransformator(Task $task)
    {
        return $this->TaskCompletedPayloadTransformator($task);
    }

    private function TaskListChangedPayloadTransformator(Task $task)
    {
        return [
            'id' => $task->getId(),
            'project_id' => $task->getProjectId(),
            'task_list_id' => $task->getTaskListId(),
            'position' => $task->getPosition(),
            'is_complete_data' => true,
        ];
    }

    private function TaskMoveToTrashPayloadTransformator(Task $task)
    {
        return [
            'id' => $task->getId(),
            'project_id' => $task->getProjectId(),
            'is_trashed' => $task->getIsTrashed(),
            'is_complete_data' => true,
        ];
    }

    private function TaskRestoredFromTrashPayloadTransformator(Task $task)
    {
        return $this->TaskMoveToTrashPayloadTransformator($task);
    }

    private function TaskReorderedPayloadTransformator(Task $task)
    {
        $ordered_ids = DB::executeFirstColumn(
            'SELECT t.id FROM tasks t WHERE t.task_list_id = ? ORDER BY t.position ASC',
            $task->getTaskListId()
        );

        return [
            'task_list_id' => $task->getTaskListId(),
            'ordered_task_ids' => $ordered_ids,
        ];
    }

    private function TaskListCreatedPayloadTransformator(TaskList $task_list)
    {
        $data = array_merge(
            $task_list->jsonSerialize(),
            [
                'is_complete_data' => true,
            ]
        );

        if ($this->calculateDataSize($data) >= self::PUSHER_PAYLOAD_LIMIT) {
            return [
                'id' => $task_list->getId(),
                'project_id' => $task_list->getProjectId(),
                'url' => $task_list->getUrlPath(),
                'is_complete_data' => false,
            ];
        } else {
            return $data;
        }
    }

    private function TaskListUpdatedPayloadTransformator(TaskList $task_list)
    {
        return $this->TaskListCreatedPayloadTransformator($task_list);
    }

    private function TaskListReorderedPayloadTransformator(TaskList $task_list)
    {
        return [
            'ordered_task_list_ids' => DB::executeFirstColumn(
                'SELECT id FROM task_lists WHERE project_id = ? ORDER BY position ASC',
                $task_list->getProjectId()
            ),
        ];
    }

    private function TaskListMoveToTrashPayloadTransformator(TaskList $task_list)
    {
        return [
            'id' => $task_list->getId(),
            'name' => $task_list->getName(),
            'project_id' => $task_list->getProjectId(),
            'is_trashed' => $task_list->getIsTrashed(),
            'is_complete_data' => true,
        ];
    }

    private function TaskListRestoredFromTrashPayloadTransformator(TaskList $task_list)
    {
        return $this->TaskListMoveToTrashPayloadTransformator($task_list);
    }

    private function TaskListCompletedPayloadTransformator(TaskList $task_list)
    {
        return [
            'id' => $task_list->getId(),
            'name' => $task_list->getName(),
            'project_id' => $task_list->getProjectId(),
            'is_completed' => $task_list->isCompleted(),
            'is_complete_data' => true,
        ];
    }

    private function TaskListReopenedPayloadTransformator(TaskList $task_list)
    {
        return $this->TaskListCompletedPayloadTransformator($task_list);
    }

    /**
     * Calculate size of data array.
     *
     * @param $data
     * @return int
     */
    public function calculateDataSize($data)
    {
        $serialized = serialize(json_encode($data));
        if (function_exists('mb_strlen')) {
            return mb_strlen($serialized, '8bit');
        } else {
            return strlen($serialized);
        }
    }
}
