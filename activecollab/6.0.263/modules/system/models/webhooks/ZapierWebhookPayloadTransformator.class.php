<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\HTML;

/**
 * @package angie.frameworks.environment
 * @subpackage models
 */
class ZapierWebhookPayloadTransformator extends WebhookPayloadTransformator
{
    /**
     * {@inheritdoc}
     */
    public function shouldTransform($url)
    {
        return strpos($url, 'zapier.com/hooks') !== false;
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
            'ProjectCreated',
            'TaskListCreated',
            'TaskCreated',
            'CommentCreated',
            'TimeRecordCreated',
            'TaskCompleted',
            'TaskListChanged',
        ];
    }

    /**
     * Transform payload when project is created.
     *
     * @param  Project $project
     * @return array
     */
    public function ProjectCreatedPayloadTransformator(Project $project)
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'created_by_id' => $project->getCreatedById(),
            'created_by_name' => $project->getCreatedByName(),
        ];
    }

    /**
     * Transform payload when task list is created.
     *
     * @param  TaskList $task_list
     * @return array
     */
    public function TaskListCreatedPayloadTransformator(TaskList $task_list)
    {
        return [
            'id' => $task_list->getId(),
            'name' => $task_list->getName(),
            'project_id' => $task_list->getProjectId(),
            'project_name' => $this->getProjectName($task_list->getProjectId()),
        ];
    }

    /**
     * Transform payload when task is created.
     *
     * @param  Task  $task
     * @return array
     */
    public function TaskCreatedPayloadTransformator(Task $task)
    {
        $assignee = $task->getAssignee();

        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'body' => HTML::toPlainText($task->getBody()),
            'project_id' => $task->getProjectId(),
            'project_name' => $this->getProjectName($task->getProjectId()),
            'task_list_id' => $task->getTaskListId(),
            'task_list_name' => $this->getTaskListName($task->getTaskListId()),
            'assignee_id' => $assignee ? $assignee->getId() : 0,
            'assignee_name' => $assignee ? $assignee->getDisplayName() : '',
        ];
    }

    /**
     * Transform payload when comment is created.
     *
     * @param  Comment $comment
     * @return array
     */
    public function CommentCreatedPayloadTransformator(Comment $comment)
    {
        return [
            'id' => $comment->getId(),
            'body' => HTML::toPlainText($comment->getBody()),
            'parent_type' => $comment->getParentType(),
            'parent_id' => $comment->getParentId(),
            'parent_name' => $comment->getParent()->getName(),
            'created_by_id' => $comment->getCreatedById(),
            'created_by_name' => $comment->getCreatedByName(),
        ];
    }

    /**
     * Transform payload when time record is created.
     *
     * @param  TimeRecord $time_record
     * @return array
     */
    public function TimeRecordCreatedPayloadTransformator(TimeRecord $time_record)
    {
        return [
            'id' => $time_record->getId(),
            'parent_type' => $time_record->getParentType(),
            'parent_id' => $time_record->getParentId(),
            'parent_name' => $time_record->getParent()->getName(),
            'job_type_id' => $time_record->getJobTypeId(),
            'value' => $time_record->getValue(),
            'description' => $time_record->getSummary(),
            'record_date' => $time_record->getRecordDate(),
            'record_user_id' => $time_record->getUserId(),
            'record_user_name' => $this->getUserDisplayName(
                $time_record->getUserId(),
                $time_record->getUserName(),
                $time_record->getUserEmail()
            ),
            'billable_status' => $time_record->getBillableStatus(),
            'created_by_id' => $time_record->getCreatedById(),
            'created_by_name' => $time_record->getCreatedByName(),
        ];
    }

    /**
     * Transform payload when task is completed.
     *
     * @param  Task  $task
     * @return array
     */
    public function TaskCompletedPayloadTransformator(Task $task)
    {
        $assignee = $task->getAssignee();

        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'project_id' => $task->getProjectId(),
            'project_name' => $this->getProjectName($task->getProjectId()),
            'task_list_id' => $task->getTaskListId(),
            'task_list_name' => $this->getTaskListName($task->getTaskListId()),
            'assignee_id' => $assignee ? $assignee->getId() : 0,
            'assignee_name' => $assignee ? $assignee->getDisplayName() : '',
            'completed_by_id' => $task->getCompletedBy()->getId(),
            'completed_by_name' => $this->getUserDisplayName(
                $task->getCompletedById(),
                $task->getCompletedByName(),
                $task->getCompletedByEmail()
            ),
        ];
    }

    /**
     * Transform payload when change task list.
     *
     * @param  Task  $task
     * @return array
     */
    public function TaskListChangedPayloadTransformator(Task $task)
    {
        $assignee = $task->getAssignee();

        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'project_id' => $task->getProjectId(),
            'project_name' => $this->getProjectName($task->getProjectId()),
            'task_list_id' => $task->getTaskListId(),
            'task_list_name' => $this->getTaskListName($task->getTaskListId()),
            'assignee_id' => $assignee ? $assignee->getId() : 0,
            'assignee_name' => $assignee ? $assignee->getDisplayName() : '',
        ];
    }

    /**
     * Return project name by project ID, without loading and hydrating an entire project instance.
     *
     * @param  int    $project_id
     * @return string
     */
    private function getProjectName($project_id)
    {
        return $this->getNameFromTable('projects', $project_id);
    }

    /**
     * Return task list name by task list ID, without loading and hydrating an entire project instance.
     *
     * @param  int    $task_list_id
     * @return string
     */
    private function getTaskListName($task_list_id)
    {
        return $this->getNameFromTable('task_lists', $task_list_id);
    }

    /**
     * Return value of name columnt from the given table, for the given ID.
     *
     * @param  string $table_name
     * @param  int    $id
     * @return string
     */
    private function getNameFromTable($table_name, $id)
    {
        return (string) DB::executeFirstCell("SELECT name FROM {$table_name} WHERE id = ?", $id);
    }

    /**
     * Return user's display name from the given arguments.
     *
     * @param  int    $id
     * @param  string $full_name
     * @param  string $email
     * @return string
     */
    private function getUserDisplayName($id, $full_name, $email)
    {
        $display_name = '';

        if ($id) {
            $display_name = Users::getUserDisplayNameById($id);
        }

        if (empty($display_name)) {
            $display_name = Users::getUserDisplayName([
                'full_name' => $full_name,
                'email' => $email,
            ]);
        }

        return $display_name;
    }
}
