<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.system
 * @subpackage model
 */
abstract class BaseProjectExport implements ProjectExportInterface
{
    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var DateTimeValue|null
     */
    protected $changes_since;

    /**
     * @var bool
     */
    protected $include_file_locations;

    /**
     * @var array
     */
    protected $project_file_locations = [];

    /**
     * @var array
     */
    protected $task_list_ids = false;
    protected $task_ids = false;
    protected $subtask_ids = false;
    protected $discussion_ids = false;
    protected $file_ids = false;
    protected $note_ids = false;
    protected $time_record_ids = false;
    protected $expense_ids = false;
    protected $comment_ids = false;
    protected $attachment_ids = false;
    protected $task_labels = false;

    /**
     * @var string
     */
    protected $user_filter = false;

    /**
     * @var WarehouseIntegration|null
     */
    protected $warehouse_integration;

    /**
     * @var string
     */
    protected $work_folder_path;

    /**
     * @param Project       $project
     * @param User          $user
     * @param DateTimeValue $changes_since
     * @param bool          $include_file_locations
     * @param string        $work_folder_path
     */
    public function __construct(
        Project &$project,
        User &$user,
        DateTimeValue $changes_since = null,
        $include_file_locations = false,
        $work_folder_path = ''
    )
    {
        $this->project = $project;
        $this->user = $user;
        $this->changes_since = $changes_since;
        $this->include_file_locations = $include_file_locations;
        $this->work_folder_path = $work_folder_path;

        $this->timestamp = DateTimeValue::now()->getTimestamp();
        $this->warehouse_integration = Integrations::findFirstByType(WarehouseIntegration::class);
    }

    /**
     * Prepare work folder path.
     *
     * @param  string               $path
     * @throws DirectoryCreateError
     */
    protected function prepareWorkFolder($path)
    {
        if (!is_dir($path)) {
            $old_umask = umask(0000);
            $folder_created = mkdir($path, 0777);
            umask($old_umask);

            if (!$folder_created) {
                throw new DirectoryCreateError($folder_created);
            }
        }
    }

    /**
     * @return array
     */
    protected function getTaskListIds()
    {
        if ($this->task_list_ids === false) {
            $this->task_list_ids = DB::executeFirstColumn(
                'SELECT id FROM task_lists WHERE project_id = ? ORDER BY id',
                $this->project->getId()
            );

            if (empty($this->task_list_ids)) {
                $this->task_list_ids = [];
            }
        }

        return $this->task_list_ids;
    }

    /**
     * @return array
     */
    protected function getTaskIds()
    {
        if ($this->task_ids === false) {
            $this->task_ids = DB::executeFirstColumn(
                'SELECT id FROM tasks WHERE project_id = ? ' . $this->getUserFilter() . ' ORDER BY id',
                $this->project->getId()
            );

            if (empty($this->task_ids)) {
                $this->task_ids = [];
            }
        }

        return $this->task_ids;
    }

    /**
     * @return string
     */
    protected function getUserFilter()
    {
        if ($this->user_filter === false) {
            $this->user_filter = $this->user instanceof Client
                ? DB::prepare('AND is_hidden_from_clients = ?', false)
                : '';
        }

        return $this->user_filter;
    }

    /**
     * @return array
     */
    protected function getSubtaskIds()
    {
        if ($this->subtask_ids === false) {
            $this->subtask_ids = DB::executeFirstColumn(
                'SELECT id FROM subtasks WHERE task_id IN (SELECT id FROM tasks WHERE project_id = ? ' . $this->getUserFilter() . ' ORDER BY id)',
                $this->project->getId()
            );

            if (empty($this->subtask_ids)) {
                $this->subtask_ids = [];
            }
        }

        return $this->subtask_ids;
    }

    /**
     * @return array
     */
    protected function getDiscussionIds()
    {
        if ($this->discussion_ids === false) {
            $this->discussion_ids = DB::executeFirstColumn(
                'SELECT id FROM discussions WHERE project_id = ? ' . $this->getUserFilter() . ' ORDER BY id',
                $this->project->getId()
            );

            if (empty($this->discussion_ids)) {
                $this->discussion_ids = [];
            }
        }

        return $this->discussion_ids;
    }

    /**
     * @return array
     */
    protected function getFileIds()
    {
        if ($this->file_ids === false) {
            $this->file_ids = DB::executeFirstColumn(
                'SELECT id FROM files WHERE project_id = ? ' . $this->getUserFilter() . ' ORDER BY id',
                $this->project->getId()
            );

            if (empty($this->file_ids)) {
                $this->file_ids = [];
            }
        }

        return $this->file_ids;
    }

    /**
     * @return array
     */
    protected function getNoteIds()
    {
        if ($this->note_ids === false) {
            $this->note_ids = DB::executeFirstColumn(
                'SELECT id FROM notes WHERE project_id = ? ' . $this->getUserFilter() . ' ORDER BY id',
                $this->project->getId()
            );

            if (empty($this->note_ids)) {
                $this->note_ids = [];
            }
        }

        return $this->note_ids;
    }

    /**
     * @return array
     */
    protected function getTimeRecordIds()
    {
        if ($this->time_record_ids === false) {
            if ($this->user instanceof Client && !$this->project->getIsClientReportingEnabled()) {
                $this->time_record_ids = [];

                return $this->time_record_ids;
            }

            if ($this->project->getIsTrackingEnabled()) {
                $task_ids = $this->getTaskIds();

                if (count($task_ids)) {
                    $this->time_record_ids = DB::executeFirstColumn(
                        "SELECT id FROM time_records WHERE (parent_type = 'Project' AND parent_id = ?) OR (parent_type = 'Task' AND parent_id IN (?)) " . $this->getFilterByUserRole() . ' ORDER BY id',
                        $this->project->getId(),
                        $task_ids
                    );
                } else {
                    $this->time_record_ids = DB::executeFirstColumn(
                        "SELECT id FROM time_records WHERE parent_type = 'Project' AND parent_id = ? " . $this->getFilterByUserRole() . ' ORDER BY id',
                        $this->project->getId()
                    );
                }
            }
        }

        if (empty($this->time_record_ids)) {
            $this->time_record_ids = [];
        }

        return $this->time_record_ids;
    }

    /**
     * @return array
     */
    protected function getExpenseIds()
    {
        if ($this->expense_ids === false) {
            if ($this->user instanceof Client && !$this->project->getIsClientReportingEnabled()) {
                $this->expense_ids = [];

                return $this->expense_ids;
            }

            if ($this->project->getIsTrackingEnabled()) {
                $task_ids = $this->getTaskIds();

                if (count($task_ids)) {
                    $this->expense_ids = DB::executeFirstColumn(
                        "SELECT id FROM expenses WHERE (parent_type = 'Project' AND parent_id = ?) OR (parent_type = 'Task' AND parent_id IN (?)) " . $this->getFilterByUserRole() . ' ORDER BY id',
                        $this->project->getId(),
                        $task_ids
                    );
                } else {
                    $this->expense_ids = DB::executeFirstColumn(
                        "SELECT id FROM expenses WHERE parent_type = 'Project' AND parent_id = ? " . $this->getFilterByUserRole() . ' ORDER BY id',
                        $this->project->getId()
                    );
                }
            }
        }

        if (empty($this->expense_ids)) {
            $this->expense_ids = [];
        }

        return $this->expense_ids;
    }

    /**
     * @return array
     */
    protected function getCommentIds()
    {
        if ($this->comment_ids === false) {
            $conditions = $this->prepareParentConditions();

            if (count($conditions)) {
                $this->comment_ids = DB::executeFirstColumn(
                    'SELECT id FROM comments WHERE ' . implode(' OR ', $conditions)
                );
            }

            if (empty($this->comment_ids)) {
                $this->comment_ids = [];
            }
        }

        return $this->comment_ids;
    }

    /**
     * @return array
     */
    protected function getAttachmentIds()
    {
        if ($this->attachment_ids === false) {
            $conditions = $this->prepareParentConditions();

            if (count($this->getCommentIds())) {
                $conditions[] = DB::prepare(
                    '(parent_type = ? AND parent_id IN (?))',
                    Comment::class,
                    $this->getCommentIds()
                );
            }

            if (count($conditions)) {
                $this->attachment_ids = DB::executeFirstColumn('SELECT id FROM attachments WHERE ' . implode(' OR ', $conditions));
            }

            if (empty($this->attachment_ids)) {
                $this->attachment_ids = [];
            }
        }

        return $this->attachment_ids;
    }

    /**
     * @return array
     */
    private function prepareParentConditions()
    {
        $conditions = [];

        if (count($this->getDiscussionIds())) {
            $conditions[] = DB::prepare(
                '(parent_type = ? AND parent_id IN (?))',
                Discussion::class,
                $this->getDiscussionIds()
            );
        }

        if (count($this->getNoteIds())) {
            $conditions[] = DB::prepare(
                '(parent_type = ? AND parent_id IN (?))',
                Note::class,
                $this->getNoteIds()
            );
        }

        if (count($this->getTaskIds())) {
            $conditions[] = DB::prepare(
                '(parent_type = ? AND parent_id IN (?))',
                Task::class,
                $this->getTaskIds()
            );
        }

        return $conditions;
    }

    /**
     * Return filter that filters out time records and expenses that user can see.
     *
     * Clients, project leaders and owners see all time records and expenses in a project. Everyone else see only
     * their-own records.
     *
     * @return string
     */
    protected function getFilterByUserRole()
    {
        return (!($this->user instanceof Client || $this->user->isOwner() || $this->project->isLeader($this->user)))
            ? DB::prepare('AND user_id = ?', $this->user->getId())
            : '';
    }
}
