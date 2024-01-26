<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

class ProjectTemplate extends BaseProjectTemplate implements RoutingContextInterface, IProjectBasedOn, ICreatedBy
{
    use RoutingContextImplementation;

    /**
     * @var int[]
     */
    private $last_project_day = [];

    /**
     * Cached day numbers and date values.
     *
     * @var string[]
     */
    private $project_day_dates = [];

    /**
     * @var array
     */
    private $elements_with_attachments = false;

    /**
     * @var array
     */
    private $element_with_labels = false;

    public function isDraft(): bool
    {
        return empty($this->getName());
    }

    /**
     * @return ProjectTemplateTaskList[]|iterable|null
     */
    public function getTaskLists(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateTaskList::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateTask[]|iterable|null
     */
    public function getTasks(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateTask::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateSubtask[]|iterable|null
     */
    public function getSubtasks(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateSubtask::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateRecurringTask[]|iterable|null
     */
    public function getRecurringTasks(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateRecurringTask::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateDiscussion[]|iterable|null
     */
    public function getDiscussions(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateDiscussion::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateFile[]|iterable|null
     */
    public function getFiles(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateFile::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateNoteGroup[]|iterable|null
     */
    public function getNoteGroups(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateNoteGroup::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return ProjectTemplateNote[]|iterable|null
     */
    public function getNotes(): ?iterable
    {
        return ProjectTemplateElements::findBy(
            [
                'type' => ProjectTemplateNote::class,
                'template_id' => $this->getId(),
            ]
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        if (empty($result['name'])) {
            $result['name'] = '';
            $result['is_draft'] = true;
        } else {
            $result['is_draft'] = false;
        }

        $result['is_scheduled'] = $this->isScheduled();

        $result['count_recurring_task'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateRecurringTask::class
        );
        $result['count_task_lists'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateTaskList::class
        );
        $result['count_tasks'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateTask::class
        );
        $result['count_subtasks'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateSubtask::class
        );
        $result['count_discussions'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateDiscussion::class
        );
        $result['count_files'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateFile::class
        );
        $result['count_notes'] = ProjectTemplateElements::countByProjectTemplate(
            $this,
            ProjectTemplateNote::class
        );

        return $result;
    }

    /**
     * Check if is one or more object of this template scheduled.
     *
     * @return bool
     */
    public function isScheduled()
    {
        return AngieApplication::cache()->getByObject(
            $this,
            'is_scheduled',
            function () {
                $rows = DB::execute(
                    'SELECT raw_additional_properties FROM project_template_elements WHERE template_id = ? AND raw_additional_properties LIKE ?',
                    $this->getId(),
                    '%due_on%'
                );

                if ($rows) {
                    foreach ($rows as $row) {
                        $attributes = unserialize($row['raw_additional_properties']);

                        if (isset($attributes['due_on']) && $attributes['due_on'] > 0) {
                            return true;
                        }
                    }
                }

                return false;
            }
        );
    }

    public function getRoutingContext(): string
    {
        return 'project_template';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'project_template_id' => $this->getId(),
        ];
    }

    /**
     * Copy template objects to destination project.
     *
     * @param  DateValue $first_day
     * @throws Exception
     */
    public function copyItems(Project &$to, User $by, $first_day = null)
    {
        if (empty($first_day)) {
            $first_day = DateValue::now();
        }

        DB::transact(
            function () use (&$to, $by, $first_day) {
                if (count($this->getMemberIds())) {
                    $members_to_add = Users::findByIds($this->getMemberIds());

                    if (count($members_to_add)) {
                        $to->addMembers($members_to_add, ['by' => $by]);
                    }
                }

                $first_day_mysql = $first_day->toMySQL();

                $task_lists = $tasks = $subtasks = $disucssions = $files = $note_groups = $notes = $recurring_tasks = [];

                $element_rows = DB::execute(
                    'SELECT `id`, `type`, `name`, `body`, `raw_additional_properties`, `position`
                        FROM `project_template_elements`
                        WHERE `template_id` = ?
                        ORDER BY `id`',
                    $this->getId()
                );

                if ($element_rows) {
                    foreach ($element_rows as $element_row) {
                        switch ($element_row['type']) {
                            case ProjectTemplateTaskList::class:
                                $task_lists[] = $this->taskListFromRow($first_day_mysql, $element_row);
                                break;
                            case ProjectTemplateTask::class:
                                $tasks[] = $this->taskFromRow($first_day_mysql, $element_row);
                                break;
                            case ProjectTemplateSubtask::class:
                                $subtasks[] = $this->subtaskFromRow($first_day_mysql, $element_row);
                                break;
                            case ProjectTemplateRecurringTask::class:
                                $recurring_tasks[] = $this->recurringTaskFromRow($element_row);
                                break;
                            case ProjectTemplateDiscussion::class:
                                $disucssions[] = $this->discussionFromRow($element_row);
                                break;
                            case ProjectTemplateNoteGroup::class:
                                $note_groups[] = $this->noteGroupFromRow($element_row);
                                break;
                            case ProjectTemplateNote::class:
                                $notes[] = $this->noteFromRow($element_row);
                                break;
                            case ProjectTemplateFile::class:
                                $files[] = $this->fileFromRow($element_row);
                                break;
                        }
                    }

                    $assignees_map = [];

                    foreach ($to->getMembers() as $member) {
                        $assignees_map[$member->getId()] = $member;
                    }

                    $task_lists_map = $this->createTaskLists($to, $task_lists);
                    $tasks_map = $this->createTasks($to, $first_day, $first_day_mysql, $assignees_map, $task_lists_map, $tasks);
                    $this->createTaskDependencies($tasks_map, $tasks);
                    $this->createRecurringTasks($to, $assignees_map, $task_lists_map, $recurring_tasks);
                    $this->createSubtasks($to, $first_day, $first_day_mysql, $assignees_map, $tasks_map, $subtasks);
                    $this->createDiscussions($to, $disucssions);
                    $note_groups_map = $this->createNoteGroups($to, $note_groups);
                    $this->createNotes($to, $notes, $note_groups_map);
                    $this->createFiles($to, $files);
                }
            }
        );
    }

    private function createTaskDependencies(array $tasks_map, array $elements)
    {
        $task_ids = array_map(function ($task) {
            return $task['id'];
        }, $elements);

        $task_dependencies = [];
        if ($task_element_dependencies = ProjectTemplateTaskDependencies::findDependenciesByElementIds($task_ids)) {
            foreach ($task_element_dependencies as $element_dependency) {
                $task_dependencies[] = TaskDependencies::create(
                    [
                        'parent_id' => $tasks_map[$element_dependency['parent_id']],
                        'child_id' => $tasks_map[$element_dependency['child_id']],
                    ]
                );
            }
        }

        return $task_dependencies;
    }

    /**
     * Load task list details from element row.
     *
     * @param  string $first_day_mysql
     * @param  array  $row
     * @return array
     */
    private function taskListFromRow($first_day_mysql, $row)
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        $due_on = isset($attributes['due_on']) ? (int) $attributes['due_on'] : 0;

        if ($due_on) {
            $this->pushLastProjectDay($first_day_mysql, $due_on);
        }

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'start_on' => isset($attributes['start_on']) ? (int) $attributes['start_on'] : 0,
            'due_on' => $due_on,
            'position' => empty($row['position']) ? 1 : $row['position'],
        ];
    }

    /**
     * Push last project data.
     *
     * @param string $first_day_mysql
     * @param int    $day
     */
    private function pushLastProjectDay($first_day_mysql, $day)
    {
        if (empty($day)) {
            return;
        }

        if (empty($this->last_project_day[$first_day_mysql]) || $this->last_project_day[$first_day_mysql] < $day) {
            $this->last_project_day[$first_day_mysql] = $day;
        }
    }

    /**
     * Prepare a task record based on task template row.
     *
     * @param  string $first_day_mysql
     * @param  array  $row
     * @return array
     */
    private function taskFromRow($first_day_mysql, $row)
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        $start_on = isset($attributes['start_on']) ? (int) $attributes['start_on'] : 0;
        $due_on = isset($attributes['due_on']) ? (int) $attributes['due_on'] : 0;

        if ($due_on) {
            $this->pushLastProjectDay($first_day_mysql, $due_on);
        }

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'body' => $row['body'],
            'task_list_id' => isset($attributes['task_list_id']) ? (int) $attributes['task_list_id'] : 0,
            'assignee_id' => isset($attributes['assignee_id']) ? (int) $attributes['assignee_id'] : 0,
            'job_type_id' => isset($attributes['job_type_id']) ? (int) $attributes['job_type_id'] : 0,
            'estimate' => isset($attributes['estimate']) ? (float) $attributes['estimate'] : 0.0,
            'start_on' => $start_on > 0 ? $start_on : $due_on, // if not set, $start_on need to be same as $due_on
            'due_on' => $due_on,
            'is_important' => isset($attributes['is_important']) && $attributes['is_important'],
            'is_hidden_from_clients' => !empty($attributes['is_hidden_from_clients']),
            'position' => $row['position'],
        ];
    }

    /**
     * Prepare a task record based on recurring task template row.
     */
    private function recurringTaskFromRow(array $row): array
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        if ($attributes['due_in'] == '') {
            $due_in = null;
            $start_in = null;
        } else {
            $due_in = isset($attributes['due_in']) ? (int) $attributes['due_in'] : 0;
            $start_in = isset($attributes['start_in']) ? (int) $attributes['start_in'] : 0;
        }

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'body' => $row['body'],
            'task_list_id' => isset($attributes['task_list_id']) ? (int) $attributes['task_list_id'] : 0,
            'assignee_id' => isset($attributes['assignee_id']) ? (int) $attributes['assignee_id'] : 0,
            'is_important' => isset($attributes['is_important']) && $attributes['is_important'],
            'is_hidden_from_clients' => !empty($attributes['is_hidden_from_clients']),
            'position' => $row['position'],
            'subtasks' => $attributes['subtasks'],
            'repeat_frequency' => $attributes['repeat_frequency'],
            'repeat_amount' => $attributes['repeat_amount'],
            'repeat_amount_extended' => $attributes['repeat_amount_extended'],
            'start_in' => $start_in > 0 ? $start_in : $due_in, // if not set, $start_in need to be same as $due_in
            'due_in' => $due_in,
            'estimate' => $attributes['estimate'],
            'job_type_id' => $attributes['job_type_id'],
        ];
    }

    /**
     * Load subtask details from element row.
     *
     * @param  string $first_day_mysql
     * @param  array  $row
     * @return array
     */
    private function subtaskFromRow($first_day_mysql, $row)
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        $due_on = isset($attributes['due_on']) ? (int) $attributes['due_on'] : 0;

        if ($due_on) {
            $this->pushLastProjectDay($first_day_mysql, $due_on);
        }

        return [
            'id' => $row['id'],
            'task_id' => isset($attributes['task_id']) ? (int) $attributes['task_id'] : 0,
            'body' => $row['body'],
            'due_on' => $due_on,
            'assignee_id' => isset($attributes['assignee_id']) ? (int) $attributes['assignee_id'] : 0,
            'position' => $row['position'],
        ];
    }

    /**
     * Load discussion details from element row.
     *
     * @param  array $row
     * @return array
     */
    private function discussionFromRow($row)
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'body' => $row['body'],
            'is_hidden_from_clients' => !empty($attributes['is_hidden_from_clients']),
            'position' => $row['position'],
        ];
    }

    /**
     * Load note group details from element row.
     *
     * @param  array $row
     * @return array
     */
    public function noteGroupFromRow($row)
    {
        return [
            'id' => $row['id'],
            'position' => $row['position'],
        ];
    }

    /**
     * Load note details from element row.
     *
     * @param  array $row
     * @return array
     */
    private function noteFromRow($row)
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'body' => $row['body'],
            'note_group_id' => isset($attributes['note_group_id']) ? (int) $attributes['note_group_id'] : 0,
            'is_hidden_from_clients' => !empty($attributes['is_hidden_from_clients']),
            'position' => $row['position'],
        ];
    }

    /**
     * Load discussion details from element row.
     *
     * @param  array $row
     * @return array
     */
    private function fileFromRow($row)
    {
        $attributes = $row['raw_additional_properties'] ? unserialize($row['raw_additional_properties']) : [];

        $result = [
            'id' => $row['id'],
            'name' => $row['name'],
            'type' => isset($attributes['type']) ? $attributes['type'] : '',
            'location' => isset($attributes['location']) ? $attributes['location'] : '',
            'mime_type' => isset($attributes['mime_type']) ? $attributes['mime_type'] : '',
            'size' => isset($attributes['size']) ? (int) $attributes['size'] : 0,
            'is_hidden_from_clients' => !empty($attributes['is_hidden_from_clients']),
            'position' => $row['position'],
            'md5' => $attributes['md5'],
        ];

        if (isset($attributes['url'])) {
            $result['url'] = $attributes['url'];
        }

        return $result;
    }

    /**
     * Create task lists using task list element data and return element -> task list ID-s map.
     *
     * @return array
     */
    private function createTaskLists(Project &$project, array $task_lists)
    {
        $project_id = $project->getId();

        $task_lists_map = [];

        if (!empty($task_lists)) {
            $first_task_list_updated = false;

            foreach ($task_lists as $k) {
                if ($first_task_list_updated) {
                    $task_list = TaskLists::create(
                        [
                            'project_id' => $project_id,
                            'name' => $k['name'],
                            'position' => $k['position'],
                        ]
                    );

                    if ($task_list instanceof TaskList) {
                        $task_lists_map[$k['id']] = $task_list->getId();
                    }
                } else {
                    $task_lists_map[$k['id']] = $this->updateFirstTaskList($project, $k)->getId();

                    $first_task_list_updated = true;
                }
            }
        }

        return $task_lists_map;
    }

    private function updateFirstTaskList(Project $project, array $task_list): TaskList
    {
        /** @var TaskList $first_task_list */
        $first_task_list = TaskLists::getFirstTaskList($project);

        $first_task_list = TaskLists::update(
            $first_task_list,
            [
                'name' => $task_list['name'],
                'position' => $task_list['position'],
            ]
        );

        return $first_task_list;
    }

    /**
     * Get day date.
     *
     * @param  string      $first_day_mysql
     * @param  int         $day
     * @return string|null
     */
    private function getProjectDayDate(DateValue $first_day, $first_day_mysql, $day)
    {
        if ($day < 1) {
            return null;
        }

        $last_project_day = $this->getLastProjectDay($first_day_mysql);

        if (empty($this->project_day_dates[$first_day_mysql]) && $last_project_day) {
            $this->project_day_dates[$first_day_mysql][1] = $first_day_mysql;

            $current_day = 2;
            $reference = DateValue::makeFromTimestamp($first_day->getTimestamp());

            while ($current_day <= $last_project_day) {
                do {
                    $reference->advance(86400);
                } while (!$reference->isWorkday() || $reference->isDayOff());

                $this->project_day_dates[$first_day_mysql][$current_day] = $reference->toMySQL();

                ++$current_day;
            }
        }

        return $this->project_day_dates[$first_day_mysql][$day];
    }

    /**
     * Return max due on that we got while loading data.
     */
    public function getLastProjectDay(string $first_day_mysql): int
    {
        return $this->last_project_day[$first_day_mysql] ?? 0;
    }

    // ---------------------------------------------------
    //  Project last day
    // ---------------------------------------------------

    private function createTasks(
        Project &$project,
        DateValue $first_day,
        string $first_day_mysql,
        array &$assignees_map,
        array $task_lists_map,
        array $tasks
    ): array {
        $project_id = $project->getId();

        usort($tasks, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        $tasks_map = [];

        $position = 1;

        $task_list = TaskLists::getFirstTaskList($project);

        foreach ($tasks as $k) {
            $job_type_id = $k['job_type_id'];
            $estimate = $k['estimate'];

            if ($job_type_id <= 0 || $estimate <= 0) {
                $job_type_id = $estimate = 0;
            }

            $task = Tasks::create(
                [
                    'project_id' => $project_id,
                    'name' => $k['name'],
                    'body' => $k['body'],
                    'task_list_id' => $task_lists_map[$k['task_list_id']] ?? $task_list->getId(),
                    'assignee_id' => $this->getAssigneeId($k['assignee_id'], $assignees_map, $project),
                    'start_on' => $this->getProjectDayDate($first_day, $first_day_mysql, $k['start_on']),
                    'due_on' => $this->getProjectDayDate($first_day, $first_day_mysql, $k['due_on']),
                    'job_type_id' => $job_type_id,
                    'estimate' => $estimate,
                    'is_important' => $k['is_important'],
                    'is_hidden_from_clients' => $k['is_hidden_from_clients'],
                    'position' => $position++,
                    'notify_subscribers' => false,
                    'is_billable' => $project->getBudgetType() !== Project::BUDGET_NOT_BILLABLE,
                ]
            );

            if ($task instanceof Task) {
                $this->cloneElementAttachments(
                    [
                        ProjectTemplateTask::class,
                        $k['id'],
                    ],
                    $task
                );
                $this->cloneTaskLabels(
                    [
                        ProjectTemplateTask::class,
                        $k['id'],
                    ],
                    $task
                );

                $tasks_map[$k['id']] = $task->getId();
            }
        }

        return $tasks_map;
    }

    public function createRecurringTasks(
        Project &$project,
        array &$assignees_map,
        array $task_lists_map,
        array $recurring_tasks
    ): array {
        $project_id = $project->getId();

        usort($recurring_tasks, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        $recurring_tasks_map = [];

        $position = 1;

        $task_list = TaskLists::getFirstTaskList($project);

        foreach ($recurring_tasks as $k) {
            /** @var ProjectTemplateRecurringTask $element_instance */
            $element_instance = DataObjectPool::get(ProjectTemplateRecurringTask::class, $k['id']);

            // Prepare labels
            /* @var Label $label */
            $labels = $element_instance->getLabels();
            $recurring_task_labels = [];

            if (!empty($labels)) {
                foreach ($labels as $label) {
                    $recurring_task_labels[] = $label->getName();
                }
            }

            // Prepare attachments
            $attachments = $element_instance->getAttachments();
            $recurring_task_attachments = [];

            if (!empty($attachments)) {
                /** @var Attachment $attachment */
                foreach ($attachments as $attachment) {
                    $recurring_task_attachments[] = ['id' => $attachment->getId()];
                }
            }

            $recurring_task = RecurringTasks::create(
                [
                    'project_id' => $project_id,
                    'name' => $k['name'],
                    'body' => $k['body'],
                    'task_list_id' => $task_lists_map[$k['task_list_id']] ?? $task_list->getId(),
                    'assignee_id' => $this->getAssigneeId($k['assignee_id'], $assignees_map, $project),
                    'is_important' => $k['is_important'],
                    'is_hidden_from_clients' => $k['is_hidden_from_clients'],
                    'position' => $position++,
                    'subtasks' => $k['subtasks'],
                    'repeat_frequency' => $k['repeat_frequency'],
                    'repeat_amount' => $k['repeat_amount'],
                    'repeat_amount_extended' => $k['repeat_amount_extended'],
                    'start_in' => $k['start_in'],
                    'due_in' => $k['due_in'],
                    'estimate' => $k['estimate'],
                    'job_type_id' => $k['job_type_id'],
                    'labels' => $recurring_task_labels,
                    'attachments' => $recurring_task_attachments,
                ]
            );

            if ($recurring_task instanceof RecurringTask) {
                $recurring_tasks_map[$k['id']] = $recurring_task->getId();
            }
        }

        return $recurring_tasks_map;
    }

    private function getAssigneeId(int $assignee_id, array &$assignees_map, Project &$project): int
    {
        if ($assignee_id && empty($assignees_map[$assignee_id])) {
            $assignee = DataObjectPool::get('User', $assignee_id);

            if ($assignee instanceof User && $assignee->isActive()) {
                if (!$project->isMember($assignee)) {
                    $project->addMembers([$assignee]);
                }

                $assignees_map[$assignee_id] = $assignee;
            } else {
                $assignees_map[$assignee_id] = 'skip';
            }
        }

        return $assignee_id && isset($assignees_map[$assignee_id]) && $assignees_map[$assignee_id] instanceof User
            ? $assignee_id
            : 0;
    }

    /**
     * Clone element attachments in the most officient way.
     *
     * @param  array|ProjectTemplateElement $element
     * @throws Exception
     * @throws InvalidParamError
     */
    private function cloneElementAttachments($element, IAttachments &$to)
    {
        if ($this->shouldCloneAttachments($element)) {
            $element_instance = DataObjectPool::get($element[0], $element[1]);

            if ($element_instance instanceof IAttachments) {
                $element_instance->cloneAttachmentsTo($to);

                if (method_exists($to, 'getProjectId')) {
                    DB::execute(
                        'UPDATE attachments SET project_id = ? WHERE parent_type = ? AND parent_id = ?',
                        $to->getProjectId(),
                        get_class($to),
                        $to->getId()
                    );
                }
            }
        }
    }

    /**
     * Return true if we should clone attachments for the given element.
     *
     * @param  array|ProjectTemplateElement $element
     * @return bool
     * @throws InvalidParamError
     */
    public function shouldCloneAttachments($element)
    {
        if ($element instanceof ProjectTemplateElement || $element instanceof ProjectTemplateRecurringTask) {
            $element_type = get_class($element);
            $element_id = $element->getId();
        } elseif (is_array($element) && count($element)) {
            [$element_type, $element_id] = $element;
        } else {
            throw new InvalidParamError('element', $element, 'Expected ProjectTemplateElement instance or type - ID array');
        }

        if ($this->elements_with_attachments === false) {
            $this->elements_with_attachments = [];

            if ($element_ids = DB::executeFirstColumn('SELECT id FROM project_template_elements WHERE template_id = ?', $this->getId())) {
                if ($rows = DB::execute('SELECT parent_type, parent_id FROM attachments WHERE parent_type IN (?) AND parent_id IN (?)', ProjectTemplateElements::getAvailableElementClasses(), $element_ids)) {
                    foreach ($rows as $row) {
                        if (empty($this->elements_with_attachments[$row['parent_type']])) {
                            $this->elements_with_attachments[$row['parent_type']] = [];
                        }

                        $this->elements_with_attachments[$row['parent_type']][] = $row['parent_id'];
                    }
                }
            }
        }

        return !empty($this->elements_with_attachments[$element_type]) && is_array($this->elements_with_attachments[$element_type]) && in_array($element_id, $this->elements_with_attachments[$element_type]);
    }

    /**
     * Clone task labels from $element to task.
     *
     * @param  ProjectTemplateTask|array $element
     * @throws InvalidParamError
     */
    private function cloneTaskLabels($element, ILabels &$to)
    {
        if ($this->shouldCloneLabels($element)) {
            $element_instance = DataObjectPool::get($element[0], $element[1]);

            if ($element_instance instanceof ILabels) {
                $element_instance->cloneLabelsTo($to);
            }
        }
    }

    /**
     * Return true if we should clone labels for the given task.
     *
     * @param  array|ProjectTemplateTask $element
     * @return bool
     * @throws InvalidParamError
     */
    public function shouldCloneLabels($element)
    {
        if ($element instanceof ProjectTemplateTask || $element instanceof ProjectTemplateRecurringTask) {
            $element_id = $element->getId();
            $element_type = get_class($element);
        } elseif (is_array($element) && count($element)) {
            [$element_type, $element_id] = $element;
        } else {
            throw new InvalidParamError('element', $element, 'Expected ProjectTemplateTask instance or type - ID array');
        }

        if ($this->element_with_labels === false) {
            $this->element_with_labels = DB::executeFirstColumn(
                'SELECT DISTINCT parent_id AS "id" FROM parents_labels WHERE parent_type = ? AND parent_id IN (SELECT id FROM project_template_elements WHERE template_id = ?)',
                $element_type,
                $this->getId()
            );

            if (empty($this->element_with_labels)) {
                $this->element_with_labels = [];
            }
        }

        return in_array($element_id, $this->element_with_labels);
    }

    private function createSubtasks(
        Project &$project,
        DateValue $first_day,
        string $first_day_mysql,
        array &$assignees_map,
        array $tasks_map,
        array $subtasks
    ): void {
        usort($subtasks, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        foreach ($subtasks as $k) {
            $task_id = $k['task_id'];

            if (empty($tasks_map[$task_id])) {
                continue;
            }

            Subtasks::create(
                [
                    'task_id' => $tasks_map[$task_id],
                    'body' => $k['body'],
                    'assignee_id' => $this->getAssigneeId($k['assignee_id'], $assignees_map, $project),
                    'due_on' => $this->getProjectDayDate($first_day, $first_day_mysql, $k['due_on']),
                ]
            );
        }
    }

    private function createDiscussions(Project &$project, array $discussions): void
    {
        usort($discussions, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        $project_id = $project->getId();

        foreach ($discussions as $k) {
            $discussion = Discussions::create(
                [
                    'project_id' => $project_id,
                    'name' => $k['name'],
                    'body' => $k['body'],
                    'is_hidden_from_clients' => $k['is_hidden_from_clients'],
                ]
            );

            if ($discussion instanceof Discussion) {
                $this->cloneElementAttachments(['ProjectTemplateDiscussion', $k['id']], $discussion);
            }
        }
    }

    /**
     * Create note groups using template data.
     *
     * @param  Project           &$project
     * @return array
     * @throws InvalidParamError
     */
    public function createNoteGroups(Project &$project, array $note_groups)
    {
        usort($note_groups, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        $note_groups_map = [];
        $project_id = $project->getId();

        foreach ($note_groups as $k) {
            $note_group = NoteGroups::create(
                [
                    'project_id' => $project_id,
                ]
            );

            if ($note_group instanceof NoteGroup) {
                $note_groups_map[$k['id']] = $note_group->getId();
            }
        }

        return $note_groups_map;
    }

    private function createNotes(Project &$project, array $notes, array $note_groups_map): void
    {
        $project_id = $project->getId();

        $grouped_notes = [];

        foreach ($notes as $k => $v) {
            if ($v['note_group_id']) {
                $grouped_notes[] = $v;
                unset($notes[$k]);
            }
        }

        // Sort in revers order and let the project handle position values
        usort($notes, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] > $b['position']) ? -1 : 1;
        });

        foreach ($notes as $k) {
            $note = Notes::create([
                'project_id' => $project_id,
                'name' => $k['name'],
                'body' => $k['body'],
                'is_hidden_from_clients' => $k['is_hidden_from_clients'],
            ]);

            if ($note instanceof Note) {
                $this->cloneElementAttachments(['ProjectTemplateNote', $k['id']], $note);
            }
        }

        // Sort in proper order
        usort($grouped_notes, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        foreach ($grouped_notes as $k) {
            $note_group_id = $k['note_group_id'];

            if (empty($note_groups_map[$note_group_id])) {
                continue;
            }

            $note = Notes::create(
                [
                    'project_id' => $project_id,
                    'note_group_id' => $note_groups_map[$note_group_id],
                    'name' => $k['name'],
                    'body' => $k['body'],
                    'is_hidden_from_clients' => $k['is_hidden_from_clients'],
                ]
            );

            if ($note instanceof Note) {
                $this->cloneElementAttachments(['ProjectTemplateNote', $k['id']], $note);
            }
        }
    }

    private function createFiles(Project &$project, array $files): void
    {
        usort($files, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return ($a['position'] > $b['position']) ? -1 : 1; // Reverse order
        });

        $project_id = $project->getId();

        foreach ($files as $k) {
            $class = $k['type'];

            if (class_exists($class)) {
                $file = new $class();

                $new_location = $k['location'];

                if ($file instanceof LocalFile) {
                    $file_path = AngieApplication::fileLocationToPath($k['location']);
                    if (is_file($file_path)) {
                        $new_location = AngieApplication::storeFile($file_path)[1];
                    }
                } elseif ($file instanceof WarehouseFile) {
                    /* @var WarehouseIntegration $warehouse_integration */
                    $warehouse_integration = Integrations::findFirstByType(WarehouseIntegration::class);

                    $new_file = $warehouse_integration
                        ->getFileApi()
                        ->duplicateFile($warehouse_integration->getStoreId(), $new_location);
                    $new_location = $new_file->getLocation();
                } else {
                    $new_location = null;
                }

                $additional_properties = [];

                if (isset($k['url'])) {
                    $additional_properties['url'] = $k['url'];
                }

                $file->setAttributes(
                    [
                        'type' => $class,
                        'project_id' => $project_id,
                        'name' => $k['name'],
                        'location' => $new_location,
                        'mime_type' => $k['mime_type'],
                        'size' => $k['size'],
                        'is_hidden_from_clients' => $k['is_hidden_from_clients'],
                        'md5' => $k['md5'],
                        'raw_additional_properties' => !empty($additional_properties) ? serialize($additional_properties) : null,
                    ]
                );
                $file->save();
            }
        }
    }

    /**
     * Delete project template.
     *
     * @param  bool      $bulk
     * @throws Exception
     */
    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Begin: drop project template @ ' . __CLASS__);

            /** @var ProjectTemplateElement[] $elements */
            if ($elements = ProjectTemplateElements::find(['conditions' => ['template_id = ?', $this->getId()]])) {
                foreach ($elements as $element) {
                    $element->delete();
                }
            }

            if ($project_ids = DB::executeFirstColumn('SELECT id FROM projects WHERE template_id = ?', $this->getId())) {
                DB::execute('UPDATE projects SET template_id = ?, updated_on = UTC_TIMESTAMP() WHERE id IN (?)', 0, $project_ids);
                Projects::clearCacheFor($project_ids);
            }

            parent::delete();

            DB::commit('Done: drop project template @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Rollback: drop project template @ ' . __CLASS__);
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isOwner() || $user->isPowerUser();
    }

    /**
     * @return bool
     */
    public function canTrash(User $user)
    {
        return $user->isOwner() || $user->isPowerUser();
    }

    /**
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isOwner();
    }
}
