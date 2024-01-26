<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Models\Identifiable;
use ActiveCollab\Foundation\Text\BodyProcessor\BodyProcessorInterface;
use ActiveCollab\Module\System\Utils\BodyProcessorResolver\BodyProcessorResolverInterface;
use Angie\Error;

final class ProjectExport extends BaseProjectExport
{
    public function export($delete_work_folder = true)
    {
        $file_path = $this->getFilePath();

        if (!is_file($file_path)) {
            $this->prepareWorkFolder($this->getWorkFolderPath());
            $this->writeSignature();
            $this->writeProject();
            $this->writeTasksLists();
            $this->writeTasks();
            $this->writeSubtasks();
            $this->writeDiscussions();
            $this->writeFiles();
            $this->writeNotes();
            $this->writeTimeRecords();
            $this->writeExpenses();
            $this->writeComments();
            $this->writeAttachments();

            $this->pack($this->getWorkFolderPath(), $file_path, $delete_work_folder);
        }

        return $file_path;
    }

    /**
     * Return destination path of the exported file.
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->getWorkFolderPath() . '.zip';
    }

    /**
     * Return work folder path.
     *
     * @return string
     */
    public function getWorkFolderPath()
    {
        if ($this->work_folder_path === '') {
            $this->work_folder_path = sprintf(
                '%s/%d-project-%d-for-%s-%d',
                WORK_PATH,
                    AngieApplication::getAccountId(),
                    $this->project->getId(),
                    $this->user instanceof Client ? 'client' : 'member',
                    $this->project->getUpdatedOn()->getTimestamp()
                );

            if ($this->changes_since) {
                $this->work_folder_path .= '-' . $this->changes_since->getTimestamp();
            }
        }

        return $this->work_folder_path;
    }

    /**
     * Prepare and write export signature file.
     */
    private function writeSignature()
    {
        file_put_contents(
            $this->getWorkFolderPath() . '/signature.json',
            json_encode(
                [
                    'timestamp' => $this->timestamp,
                    'changes_since' => $this->changes_since instanceof DateTimeValue
                        ? $this->changes_since->getTimestamp()
                        : 0,
                    'export_routine_version' => ProjectExportInterface::EXPORT_ROUTINE_VERSION,
                ]
            )
        );
    }

    /**
     * Prepare and write project.json file.
     *
     * @throws FileCreateError
     */
    private function writeProject()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/project.json', 'a')) {
            $created_by = $this->project->getCreatedBy();
            $updated_by = $this->project->getUpdatedBy();
            $completed_by = $this->project->getCompletedBy();

            $project_json = json_encode([
                'id' => $this->project->getId(),
                'name' => $this->project->getName(),
                'body' => $this->project->getBody(),
                'body_formatted' => nl2br($this->project->getBody()),
                'category_id' => $this->project->getCategoryId(),
                'company_id' => $this->project->getCompanyId(),
                'currency_id' => $this->project->getCurrencyId(),
                'label_id' => $this->project->getLabelId(),
                'leader_id' => $this->project->getLeaderId(),
                'email' => $this->project->getMailToProjectEmail(),
                'is_trashed' => $this->project->getIsTrashed(),
                'is_tracking_enabled' => $this->project->getIsTrackingEnabled(),
                'is_client_reporting_enabled' => $this->project->getIsClientReportingEnabled(),

                'created_on' => $this->project->getCreatedOn()->getTimestamp(),
                'created_by_id' => $created_by ? $created_by->getId() : 0,
                'created_by_name' => $created_by ? $created_by->getDisplayName() : (string) $this->project->getCreatedByName(),
                'created_by_email' => $created_by ? $created_by->getEmail() : (string) $this->project->getCreatedByEmail(),

                'updated_on' => $this->project->getUpdatedOn()->getTimestamp(),
                'updated_by_id' => $updated_by ? $updated_by->getId() : 0,
                'updated_by_name' => $updated_by ? $updated_by->getDisplayName() : (string) $this->project->getUpdatedByName(),
                'updated_by_email' => $updated_by ? $updated_by->getEmail() : (string) $this->project->getUpdatedByEmail(),

                'completed_on' => $this->project->getCompletedOn() ? $this->project->getCompletedOn()->getTimestamp() : 0,
                'completed_by_id' => $completed_by ? $completed_by->getId() : 0,
                'completed_by_name' => $completed_by ? $completed_by->getDisplayName() : (string) $this->project->getCompletedByName(),
                'completed_by_email' => $completed_by ? $completed_by->getEmail() : (string) $this->project->getCompletedByEmail(),
            ]);

            fwrite($file_handle, mb_substr($project_json, 0, mb_strlen($project_json) - 1));
            unset($project_json);

            fwrite($file_handle, ',"member_ids":' . json_encode($this->project->getMemberIds()));
            fwrite($file_handle, ',"task_list_ids":' . json_encode($this->getTaskListIds()));
            fwrite($file_handle, ',"task_ids":' . json_encode($this->getTaskIds()));
            fwrite($file_handle, ',"subtask_ids":' . json_encode($this->getSubtaskIds()));
            fwrite($file_handle, ',"discussion_ids":' . json_encode($this->getDiscussionIds()));
            fwrite($file_handle, ',"file_ids":' . json_encode($this->getFileIds()));
            fwrite($file_handle, ',"note_ids":' . json_encode($this->getNoteIds()));
            fwrite($file_handle, ',"time_record_ids":' . json_encode($this->getTimeRecordIds()));
            fwrite($file_handle, ',"expense_ids":' . json_encode($this->getExpenseIds()));
            fwrite($file_handle, ',"comment_ids":' . json_encode($this->getCommentIds()));
            fwrite($file_handle, ',"attachment_ids":' . json_encode($this->getAttachmentIds()));

            fwrite($file_handle, '}');

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/project.json');
        }
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
        return (!($this->user instanceof Client || $this->user->isOwner() || $this->project->isLeader($this->user))) ? DB::prepare('AND user_id = ?', $this->user->getId()) : '';
    }

    /**
     * Write task_lists.json.
     */
    private function writeTasksLists()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/task_lists.json', 'a')) {
            $task_list_ids = $this->getTaskListIds();

            if (empty($task_list_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM task_lists WHERE id IN (?) $changes_since_filter ORDER BY id", $task_list_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        fwrite($file_handle, json_encode(array_merge([
                            'id' => $row['id'],
                            'project_id' => $row['project_id'],
                            'name' => $row['name'],
                            'start_on' => $row['start_on'] ? strtotime($row['start_on']) : 0,
                            'due_on' => $row['due_on'] ? strtotime($row['due_on']) : 0,
                            'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                            'position' => (int) $row['position'],
                            'is_trashed' => (bool) $row['is_trashed'],
                        ], $this->actionOnByToArray('created', $row), $this->actionOnByToArray('completed', $row))));
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/task_lists.json');
        }
    }

    /**
     * @param  string $action
     * @param  array  $row
     * @return array
     */
    private function actionOnByToArray($action, array &$row)
    {
        return [
            "{$action}_on" => isset($row["{$action}_on"]) && $row["{$action}_on"] ? strtotime($row["{$action}_on"]) : 0,
            "{$action}_by_id" => isset($row["{$action}_by_id"]) && $row["{$action}_by_id"] ? $row["{$action}_by_id"] : 0,
            "{$action}_by_name" => isset($row["{$action}_by_name"]) && $row["{$action}_by_name"] ? (string) $row["{$action}_by_name"] : '',
            "{$action}_by_email" => isset($row["{$action}_by_email"]) && $row["{$action}_by_email"] ? (string) $row["{$action}_by_email"] : '',
        ];
    }

    /**
     * Write tasks.json.
     */
    private function writeTasks()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/tasks.json', 'a')) {
            $task_ids = $this->getTaskIds();

            if (empty($task_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM tasks WHERE id IN (?) $changes_since_filter ORDER BY id", $task_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        fwrite(
                            $file_handle,
                            json_encode(
                                array_merge(
                                    [
                                        'id' => $row['id'],
                                        'project_id' => $row['project_id'],
                                        'name' => $row['name'],
                                        'body' => (string) $row['body'],
                                        'body_formatted' => $this->getFormattedBody($row['body'], Task::class, $row['id']),
                                        'task_number' => $row['task_number'],
                                        'task_list_id' => $row['task_list_id'],
                                        'label_ids' => $this->getLabelsForTask($row['id']),
                                        'assignee_id' => $row['assignee_id'],
                                        'delegated_by_id' => $row['delegated_by_id'],
                                        'start_on' => $row['start_on'] ? strtotime($row['start_on']) : 0,
                                        'due_on' => $row['due_on'] ? strtotime($row['due_on']) : 0,
                                        'job_type_id' => $row['job_type_id'] ? $row['job_type_id'] : 0,
                                        'estimate' => $row['estimate'] ? $row['estimate'] : 0,
                                        'is_important' => (bool) $row['is_important'],
                                        'is_trashed' => (bool) $row['is_trashed'],
                                        'is_hidden_from_clients' => (bool) $row['is_hidden_from_clients'],
                                        'position' => (int) $row['position'],
                                        'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                                    ],
                                    $this->actionOnByToArray('created', $row),
                                    $this->actionOnByToArray('completed', $row)
                                )
                            )
                        );
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/tasks.json');
        }
    }

    /**
     * Return label ID-s for the given task.
     *
     * @param  int   $task_id
     * @return array
     */
    private function getLabelsForTask($task_id)
    {
        if ($this->task_labels === false) {
            $this->task_labels = [];
            $task_ids = $this->getTaskIds();

            if (count($task_ids)) {
                if ($rows = DB::execute("SELECT parent_id, label_id FROM parents_labels WHERE parent_type = 'Task' AND parent_id IN (?) ORDER BY parent_id, label_id", $task_ids)) {
                    foreach ($rows as $row) {
                        if (empty($this->task_labels[$row['parent_id']])) {
                            $this->task_labels[$row['parent_id']] = [];
                        }

                        $this->task_labels[$row['parent_id']][] = $row['label_id'];
                    }
                }
            }
        }

        return isset($this->task_labels[$task_id]) ? $this->task_labels[$task_id] : [];
    }

    /**
     * Write subtasks.
     */
    public function writeSubtasks()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/subtasks.json', 'a')) {
            $subtask_ids = $this->getSubtaskIds();

            if (empty($subtask_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM subtasks WHERE id IN (?) $changes_since_filter ORDER BY id", $subtask_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        fwrite($file_handle, json_encode(array_merge([
                            'id' => $row['id'],
                            'task_id' => $row['task_id'],
                            'body' => (string) $row['body'],
                            'assignee_id' => $row['assignee_id'],
                            'delegated_by_id' => $row['delegated_by_id'],
                            'is_trashed' => (bool) $row['is_trashed'],
                            'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                        ], $this->actionOnByToArray('created', $row), $this->actionOnByToArray('completed', $row))));
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/tasks.json');
        }
    }

    /**
     * Write discussions.json.
     */
    private function writeDiscussions()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/discussions.json', 'a')) {
            $discussion_ids = $this->getDiscussionIds();

            if (empty($discussion_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM discussions WHERE id IN (?) $changes_since_filter ORDER BY id", $discussion_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        fwrite(
                            $file_handle,
                            json_encode(
                                array_merge(
                                    [
                                        'id' => $row['id'],
                                        'project_id' => $row['project_id'],
                                        'name' => $row['name'],
                                        'body' => (string) $row['body'],
                                        'body_formatted' => $this->getFormattedBody($row['body'], Discussion::class, $row['id']),
                                        'is_trashed' => (bool) $row['is_trashed'],
                                        'is_hidden_from_clients' => (bool) $row['is_hidden_from_clients'],
                                        'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                                    ],
                                    $this->actionOnByToArray('created', $row)
                                )
                            )
                        );
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/discussions.json');
        }
    }

    /**
     * Write files.json.
     */
    private function writeFiles()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/files.json', 'a')) {
            $file_ids = $this->getFileIds();

            if (empty($file_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM files WHERE id IN (?) $changes_since_filter ORDER BY id", $file_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        if ($row['location']) {
                            $this->project_file_locations[] = $row['location'];
                        }

                        $created_on = DateTimeValue::makeFromString($row['created_on']);

                        fwrite($file_handle, json_encode(array_merge([
                            'id' => $row['id'],
                            'type' => $row['type'],
                            'project_id' => $row['project_id'],
                            'name' => $row['name'],
                            'mime_type' => trim($row['mime_type']),
                            'md5' => (string) $row['md5'],
                            'thumbnail_url' => $this->locationToThumbnailUrl($row['type'], $row['md5'], $row['location'], $row['name']),
                            'download_url' => $this->locationToDownloadUrl($row['type'], 'files', $row['location'], $row['id'], $row['size'], $row['md5'], unserialize($row['raw_additional_properties']), $created_on),
                            'preview_url' => $this->locationToPreviewUrl($row['type'], 'files', $row['location'], $row['id'], $row['size'], $row['md5'], unserialize($row['raw_additional_properties']), $created_on),
                            'size' => (int) $row['size'],
                            'is_trashed' => (bool) $row['is_trashed'],
                            'is_hidden_from_clients' => (bool) $row['is_hidden_from_clients'],
                            'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                        ], $this->fileLocationForMerge($row['location']), $this->actionOnByToArray('created', $row))));
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/files.json');
        }
    }

    /**
     * Return file location for merge with other data, based on export settings.
     *
     * @param  string $location
     * @return array
     */
    private function fileLocationForMerge($location)
    {
        return $this->include_file_locations ? ['location' => $location] : [];
    }

    /**
     * Write notes.json.
     */
    private function writeNotes()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/notes.json', 'a')) {
            $note_ids = $this->getNoteIds();

            if (empty($note_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM notes WHERE id IN (?) $changes_since_filter ORDER BY id", $note_ids)) {
                    $first = true;

                    $note_contributors = Notes::bulkGetContributorIds($rows);

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        fwrite(
                            $file_handle,
                            json_encode(
                                array_merge(
                                    [
                                        'id' => $row['id'],
                                        'project_id' => $row['project_id'],
                                        'name' => $row['name'],
                                        'body' => (string) $row['body'],
                                        'body_formatted' => $this->getFormattedBody($row['body'], Note::class, $row['id']),
                                        'note_group_id' => $row['note_group_id'],
                                        'position' => (int) $row['position'],
                                        'is_trashed' => (bool) $row['is_trashed'],
                                        'is_hidden_from_clients' => (bool) $row['is_hidden_from_clients'],
                                        'contributors' => isset($note_contributors[$row['id']]) ? $note_contributors[$row['id']] : [],
                                        'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                                    ],
                                    $this->actionOnByToArray('created', $row)
                                )
                            )
                        );
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/notes.json');
        }
    }

    /**
     * Write project time records to time_records.json.
     */
    private function writeTimeRecords()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/time_records.json', 'a')) {
            if ($this->project->getIsTrackingEnabled()) {
                $time_record_ids = $this->getTimeRecordIds();

                if (count($time_record_ids)) {
                    $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                    if ($rows = DB::execute("SELECT * FROM time_records WHERE id IN (?) $changes_since_filter ORDER BY id", $time_record_ids)) {
                        $first = true;

                        foreach ($rows as $row) {
                            if ($first) {
                                fwrite($file_handle, '[');
                                $first = false;
                            } else {
                                fwrite($file_handle, ',');
                            }

                            fwrite($file_handle, json_encode(array_merge([
                                'id' => $row['id'],
                                'parent_type' => $row['parent_type'],
                                'parent_id' => $row['parent_id'],
                                'job_type_id' => $row['job_type_id'],
                                'record_date' => strtotime($row['record_date']),
                                'value' => (float) $row['value'],
                                'summary' => (string) $row['summary'],
                                'billable_status' => (int) $row['billable_status'],
                                'user_id' => $row['user_id'],
                                'user_name' => (string) $row['user_name'],
                                'user_email' => (string) $row['user_email'],
                                'is_trashed' => (bool) $row['is_trashed'],
                                'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                            ], $this->actionOnByToArray('created', $row))));
                        }

                        fwrite($file_handle, ']');
                    } else {
                        fwrite($file_handle, '[]');
                    }
                } else {
                    fwrite($file_handle, '[]');
                }
            } else {
                fwrite($file_handle, '[]');
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/time_records.json');
        }
    }

    /**
     * Write project expenses to expenses.json.
     */
    private function writeExpenses()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/expenses.json', 'a')) {
            if ($this->project->getIsTrackingEnabled()) {
                $expense_ids = $this->getExpenseIds();

                if (count($expense_ids)) {
                    $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                    if ($rows = DB::execute("SELECT * FROM expenses WHERE id IN (?) $changes_since_filter ORDER BY id", $expense_ids)) {
                        $first = true;

                        foreach ($rows as $row) {
                            if ($first) {
                                fwrite($file_handle, '[');
                                $first = false;
                            } else {
                                fwrite($file_handle, ',');
                            }

                            fwrite($file_handle, json_encode(array_merge([
                                'id' => $row['id'],
                                'parent_type' => $row['parent_type'],
                                'parent_id' => $row['parent_id'],
                                'category_id' => $row['category_id'],
                                'record_date' => strtotime($row['record_date']),
                                'value' => (float) $row['value'],
                                'summary' => (string) $row['summary'],
                                'billable_status' => (int) $row['billable_status'],
                                'user_id' => $row['user_id'],
                                'user_name' => (string) $row['user_name'],
                                'user_email' => (string) $row['user_email'],
                                'is_trashed' => (bool) $row['is_trashed'],
                                'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                            ], $this->actionOnByToArray('created', $row))));
                        }

                        fwrite($file_handle, ']');
                    } else {
                        fwrite($file_handle, '[]');
                    }
                } else {
                    fwrite($file_handle, '[]');
                }
            } else {
                fwrite($file_handle, '[]');
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/expenses.json');
        }
    }

    /**
     * Write comments.json.
     */
    private function writeComments()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/comments.json', 'a')) {
            $comment_ids = $this->getCommentIds();

            if (empty($comment_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND updated_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM comments WHERE id IN (?) $changes_since_filter ORDER BY id", $comment_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        fwrite($file_handle, json_encode(array_merge([
                            'id' => $row['id'],
                            'parent_type' => $row['parent_type'],
                            'parent_id' => $row['parent_id'],
                            'body' => (string) $row['body'],
                            'body_formatted' => $this->getFormattedBody($row['body'], Comment::class, $row['id']),
                            'is_trashed' => (bool) $row['is_trashed'],
                            'updated_on' => $row['updated_on'] ? strtotime($row['updated_on']) : strtotime($row['created_on']),
                        ], $this->actionOnByToArray('created', $row))));
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/comments.json');
        }
    }

    // ---------------------------------------------------
    //  Paths
    // ---------------------------------------------------

    /**
     * Write project attachment info to attachments.json.
     */
    private function writeAttachments()
    {
        if ($file_handle = fopen($this->getWorkFolderPath() . '/attachments.json', 'a')) {
            $attachment_ids = $this->getAttachmentIds();

            if (empty($attachment_ids)) {
                fwrite($file_handle, '[]');
            } else {
                $changes_since_filter = $this->changes_since ? DB::prepare(' AND created_on >= ?', $this->changes_since) : '';

                if ($rows = DB::execute("SELECT * FROM attachments WHERE id IN (?) $changes_since_filter ORDER BY id", $attachment_ids)) {
                    $first = true;

                    foreach ($rows as $row) {
                        if ($first) {
                            fwrite($file_handle, '[');
                            $first = false;
                        } else {
                            fwrite($file_handle, ',');
                        }

                        if ($row['location']) {
                            $this->project_file_locations[] = $row['location'];
                        }

                        $created_on = DateTimeValue::makeFromString($row['created_on']);

                        fwrite($file_handle, json_encode(array_merge([
                            'id' => $row['id'],
                            'type' => $row['type'],
                            'parent_type' => $row['parent_type'],
                            'parent_id' => $row['parent_id'],
                            'name' => $row['name'],
                            'mime_type' => trim($row['mime_type']),
                            'md5' => (string) $row['md5'],
                            'thumbnail_url' => $this->locationToThumbnailUrl($row['type'], $row['md5'], $row['location'], $row['name']),
                            'download_url' => $this->locationToDownloadUrl($row['type'], 'attachments', $row['location'], $row['id'], $row['size'], $row['md5'], unserialize($row['raw_additional_properties']), $created_on),
                            'preview_url' => $this->locationToPreviewUrl($row['type'], 'attachments', $row['location'], $row['id'], $row['size'], $row['md5'], unserialize($row['raw_additional_properties']), $created_on),
                            'size' => (int) $row['size'],
                            'is_trashed' => false,
                        ], $this->fileLocationForMerge($row['location']), $this->actionOnByToArray('created', $row))));
                    }

                    fwrite($file_handle, ']');
                } else {
                    fwrite($file_handle, '[]');
                }
            }

            fclose($file_handle);
        } else {
            throw new FileCreateError($this->getWorkFolderPath() . '/attachments.json');
        }
    }

    /**
     * @param        $work_path
     * @param        $file_path
     * @param  bool  $delete_work_folder
     * @throws Error
     */
    private function pack($work_path, $file_path, $delete_work_folder = true)
    {
        if (is_file($file_path)) {
            @unlink($file_path);
        }

        $zip = new PclZip($file_path);

        if (!$zip->add(get_files($work_path, null, true), PCLZIP_OPT_REMOVE_PATH, WORK_PATH)) {
            throw new Error('Could not pack files');
        }

        if (DIRECTORY_SEPARATOR != '\\') {
            @chmod($file_path, 0777);
        }

        if ($delete_work_folder) {
            safe_delete_dir($work_path, WORK_PATH);
        }
    }

    /**
     * Return file or attachment thumbnail URL based on file location and name.
     *
     * @param  string $type
     * @param  string $md5
     * @param  string $location
     * @param  string $name
     * @return string
     */
    private function locationToThumbnailUrl($type, $md5, $location, $name)
    {
        if (in_array($type, [WarehouseAttachment::class, WarehouseFile::class])) {
            $thumbnail_url = $this->warehouse_integration->prepareFileThumbnailUrl($location, $md5, '--WIDTH--', '--HEIGHT--');
        } elseif (in_array($type, [DropboxAttachment::class, DropboxFile::class, GoogleDriveAttachment::class, GoogleDriveFile::class])) {
            $thumbnail_url = null;
        } else {
            $thumbnail_url = Thumbnails::getUrl(AngieApplication::fileLocationToPath($location), $location, $name, '--WIDTH--', '--HEIGHT--', '--SCALE--');
        }

        return $thumbnail_url;
    }

    /**
     * Return file or attachment download URL.
     *
     * @param  string        $type
     * @param  string        $context
     * @param  string        $location
     * @param  string        $id
     * @param  string        $size
     * @param  string        $md5
     * @param  array         $raw_additional_properties
     * @param  DateTimeValue $created_on
     * @return string
     */
    private function locationToDownloadUrl($type, $context, $location, $id, $size, $md5, $raw_additional_properties, $created_on)
    {
        if (in_array($type, [WarehouseAttachment::class, WarehouseFile::class])) {
            $download_url = $this->warehouse_integration->prepareFileDownloadUrl($location, $md5);
        } elseif (in_array($type, [DropboxAttachment::class, DropboxFile::class, GoogleDriveAttachment::class, GoogleDriveFile::class])) {
            $download_url = $raw_additional_properties['url'];
        } else {
            $proxy_data = [
                'context' => $context,
                'id' => $id,
                'size' => $size,
                'md5' => $md5,
                'timestamp' => $created_on instanceof DateTimeValue ? $created_on->toMySQL() : '',
                'force' => true,
            ];

            $download_url = AngieApplication::getProxyUrl('download_file', AttachmentsFramework::INJECT_INTO, $proxy_data);
        }

        return $download_url;
    }

    /**
     * Return file or attachment preview URL.
     *
     * @param  string        $type
     * @param  string        $context
     * @param  string        $location
     * @param  string        $id
     * @param  string        $size
     * @param  string        $md5
     * @param  string        $raw_additional_properties
     * @param  DateTimeValue $created_on
     * @return string
     */
    private function locationToPreviewUrl($type, $context, $location, $id, $size, $md5, $raw_additional_properties, $created_on)
    {
        if (in_array($type, [WarehouseAttachment::class, WarehouseFile::class])) {
            $preview_url = $this->warehouse_integration->prepareFilePreviewUrl($location, $md5);
        } elseif (in_array($type, [DropboxAttachment::class, DropboxFile::class, GoogleDriveAttachment::class, GoogleDriveFile::class])) {
            $preview_url = $raw_additional_properties['url'];
        } else {
            $proxy_data = [
                'context' => $context,
                'id' => $id,
                'size' => $size,
                'md5' => $md5,
                'timestamp' => $created_on instanceof DateTimeValue ? $created_on->toMySQL() : '',
                'force' => false,
            ];

            $preview_url = AngieApplication::getProxyUrl('forward_preview', AttachmentsFramework::INJECT_INTO, $proxy_data);
        }

        return $preview_url;
    }

    /**
     * Return locations of all files that we found in project during export.
     *
     * @return array
     */
    public function getFileLocations()
    {
        return $this->project_file_locations;
    }

    private $body_processor;

    private function getBodyProcessor(): BodyProcessorInterface
    {
        if (empty($this->body_processor)) {
            $this->body_processor = AngieApplication::getContainer()
                ->get(BodyProcessorResolverInterface::class)
                    ->resolve(true);
        }

        return $this->body_processor;
    }

    private function getFormattedBody(?string $body, string $type, int $id): string
    {
        return $this->getBodyProcessor()->processForDisplay(
            (string) $body,
            new Identifiable($type, $id),
            BodyProcessorInterface::DISPLAY_SCEEEN
        )->getProcessedHtml();
    }
}
