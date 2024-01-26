<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\RouterInterface;

/**
 * Morning paper.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
final class MorningPaper
{
    // Event categories
    const PREV = 'prev_business_day';
    const TODAY = 'today';
    const LATE = 'late';

    // Event types
    const TASK_CREATED = 'task_created';
    const TASK_COMPLETED = 'task_completed';
    const PROJECT_STARTED = 'project_started';
    const OBJECT_DISCUSSED = 'object_discussed';
    const FILE_UPLOADED = 'file_uploaded';
    const NOTE_CREATED = 'note_created';

    const TASK_DUE = 'task_due';
    const AVAILABILITY_RECORD_DUE = 'availability_record_due';
    const CALENDAR_EVENT_DUE = 'calendar_event_due';
    const TASK_LATE = 'task_late';
    /**
     * @var array
     */
    private static $user_names = [];
    /**
     * Discussion URL pattern.
     *
     * @var string
     */
    private static $discussion_url_pattern;
    /**
     * File URL pattern.
     *
     * @var string
     */
    private static $file_url_pattern;
    /**
     * Note URL pattern.
     *
     * @var string
     */
    private static $note_url_pattern;
    /**
     * Task URL pattern.
     *
     * @var string
     */
    private static $task_url_pattern;
    /**
     * Calendar event URL pattern.
     *
     * @var string
     */
    private static $calendar_event_url_pattern;

    /**
     * Send given day data to the users.
     *
     * @param DateValue $day
     * @param User[]    $users
     */
    public static function send(DateValue $day, $users = null)
    {
        if (self::shouldSend($day)) {
            if ($users === null) {
                $users = Users::findBySQL('SELECT * FROM users WHERE is_archived = ? AND is_trashed = ? AND type IN (?)', false, false, self::whoCanReceiveMorningPaper());
            }

            if ($users && is_foreachable($users)) {
                $snapshot = self::getSnapshot($day);

                foreach ($users as $user) {
                    if ($user instanceof Client) {
                        continue;
                    }

                    if (ConfigOptions::getValueFor('notifications_user_send_morning_paper', $user)) {
                        [$late_data, $today_data, $prev_data] = $snapshot->getDataFor(
                            $user,
                            $user->isOwner() && ConfigOptions::getValueFor('morning_paper_include_all_projects', $user)
                        );

                        if ($late_data || $today_data || $prev_data) {
                            /** @var MorningPaperNotification $notification */
                            $notification = AngieApplication::notifications()->notifyAbout('system/morning_paper');
                            $notification->setPaperDay($day)
                                ->setPreviousDay(new DateValue($snapshot->getPreviousDay()))
                                ->setPaperData($prev_data, $today_data, $late_data)
                                ->sendToUsers($user);
                        }
                    }
                }
            }
        }

        ConfigOptions::setValue('morning_paper_last_activity', time());
    }

    /**
     * Return true if we should send morning paper for this particular day.
     *
     * @param  DateValue $day
     * @return bool
     */
    private static function shouldSend(DateValue $day)
    {
        return $day->isWorkday() && !$day->isDayOff();
    }

    /**
     * Return list of roles that can use morning paper feature.
     *
     * @return array
     */
    public static function whoCanReceiveMorningPaper()
    {
        return [Owner::class, Member::class];
    }

    /**
     * Return snapshot for a given day.
     *
     * @param  DateValue            $day
     * @return MorningPaperSnapshot
     * @throws InvalidParamError
     */
    public static function getSnapshot(DateValue $day)
    {
        if ($day->isWorkday() && !$day->isDayOff()) {
            return self::createDaySnapshot($day);
        } else {
            throw new InvalidParamError('day', $day, 'Day should be a work day');
        }
    }

    /**
     * Return day snapshot.
     *
     * @param  DateValue            $day
     * @return MorningPaperSnapshot
     */
    private static function createDaySnapshot(DateValue $day)
    {
        $pre_boundaries = self::getPreviousBusinessDayBoundaries($day);
        $today_boundaries = self::getTodayBoundaries($day);

        $snapshot_data = [
            self::PREV => [
                'date' => $pre_boundaries[2]->toMySQL(),
                'boundaries' => ['from' => $pre_boundaries[0]->toMySQL(), 'to' => $pre_boundaries[1]->toMySQL()],
                'events' => [],
            ],
            self::TODAY => [
                'boundaries' => ['from' => $today_boundaries[0]->toMySQL(), 'to' => $today_boundaries[1]->toMySQL()],
                'availability_records' => [],
                'calendar_events' => [],
                'events' => [],
            ],
            self::LATE => ['events' => []],
        ];

        self::queryUsers();
        self::queryCalendarEvents($snapshot_data, $day);
        self::queryAvailabilityRecords($snapshot_data, $day);

        $project_ids = DB::executeFirstColumn(
            'SELECT id FROM projects WHERE (completed_on IS NULL OR completed_on >= ?) AND is_sample = ?',
            $pre_boundaries[0],
            false
        );

        if (!empty($project_ids)) {
            $escaped_project_ids = DB::escape($project_ids);

            self::queryTasks($snapshot_data, $day, $escaped_project_ids);
            self::queryPrevBusinessDayTasks($snapshot_data, $pre_boundaries[0], $pre_boundaries[1], $escaped_project_ids);
            self::queryPrevBusinessDayDiscussions($snapshot_data, $pre_boundaries[0], $pre_boundaries[1], $escaped_project_ids);
            self::queryPrevBusinessDayNotes($snapshot_data, $pre_boundaries[0], $pre_boundaries[1], $escaped_project_ids);
            self::queryPrevBusinessDayFiles($snapshot_data, $pre_boundaries[0], $pre_boundaries[1], $escaped_project_ids);
        }

        return new MorningPaperSnapshot($snapshot_data);
    }

    /**
     * Return previous business day boundaries.
     *
     * @param  DateValue       $day
     * @return DateTimeValue[]
     */
    private static function getPreviousBusinessDayBoundaries(DateValue $day)
    {
        $copy = clone $day;

        do {
            $copy->advance(-86400);
        } while (!$copy->isWorkday() || $copy->isDayOff());

        $second_copy = clone $day;
        $second_copy->advance(-86400);

        return [$copy->beginningOfDay(), $second_copy->endOfDay(), $copy];
    }

    /**
     * Return today business day boundaries.
     *
     * @param  DateValue       $day
     * @return DateTimeValue[]
     */
    public static function getTodayBoundaries(DateValue $day)
    {
        return [$day->beginningOfDay(), $day->endOfDay()];
    }

    /**
     * Query user info.
     */
    private static function queryUsers()
    {
        foreach (DB::execute('SELECT id, first_name, last_name, email FROM users WHERE is_archived = ? AND is_trashed = ?', false, false) as $row) {
            self::$user_names[(int) $row['id']] = Users::getUserDisplayName($row, true);
        }
    }

    /**
     * Query task list changes.
     *
     * @param array     $snapshot_data
     * @param DateValue $day
     * @param string    $escaped_project_ids
     */
    private static function queryTasks(&$snapshot_data, DateValue $day, $escaped_project_ids)
    {
        // Get tasks that are due today or late
        $rows = DB::execute(
            "SELECT id, project_id, name, start_on, due_on, assignee_id, task_number, DATEDIFF(due_on, ?) AS 'diff', completed_on, start_on, due_on
                 FROM tasks
                 WHERE project_id IN ($escaped_project_ids)
                 AND is_trashed = ?
                 AND completed_on IS NULL
                 AND assignee_id > 0
                 AND ((due_on IS NOT NULL AND due_on <= ?) OR (start_on IS NOT NULL AND start_on <= ?))
                 ORDER BY due_on, start_on",
            $day,
            false,
            $day,
            $day);
        if ($rows) {
            $rows->setCasting(['due_on' => DBResult::CAST_DATE, 'start_on' => DBResult::CAST_DATE, 'diff' => DBResult::CAST_INT]);

            foreach ($rows as $row) {
                $event = $row['diff'] < 0 ? self::TASK_LATE : self::TASK_DUE;

                self::logProjectEvent($snapshot_data, $event, $row['due_on'], self::taskRowToEventDetails($row));
            }
        }
    }

    /**
     * Log an event.
     *
     * @param  array             $data
     * @param  string            $event
     * @param  DateTimeValue     $event_timestamp
     * @param  array             $event_details
     * @param  int|null          $by_id
     * @param  int|null          $by_name
     * @param  int|null          $by_email
     * @throws InvalidParamError
     */
    private static function logProjectEvent(&$data, $event, $event_timestamp, $event_details, $by_id = null, $by_name = null, $by_email = null)
    {
        switch ($event) {
            case self::TASK_CREATED:
            case self::TASK_COMPLETED:
            case self::PROJECT_STARTED:
            case self::FILE_UPLOADED:
            case self::NOTE_CREATED:
            case self::OBJECT_DISCUSSED:
                $where = self::PREV;
                break;
            case self::TASK_LATE:
                $where = self::LATE;
                break;
            default:
                $where = self::TODAY;
        }

        if (empty($event_details['project_id'])) {
            throw new InvalidParamError('event_details', $event_details, 'project_id is required');
        }

        $project_id = $event_details['project_id'];

        $event_details['action_by_id'] = $by_id; // Make sure that we have action by ID set

        if ($by_id) {
            $event_details['action_by'] = isset(self::$user_names[$by_id]) && self::$user_names[$by_id] ? self::$user_names[$by_id] : Users::getUserDisplayName([
                'full_name' => $by_name,
                'email' => $by_email,
            ], true);
        } else {
            $event_details['action_by'] = null;
        }

        if (empty($data[$where]['events'][$project_id])) {
            $data[$where]['events'][$project_id] = [];
        }

        $data[$where]['events'][$project_id][] = array_merge(['event' => $event, 'timestamp' => $event_timestamp->toMySQL()], $event_details);
    }

    /**
     * Return task URL.
     *
     * @param  int    $project_id
     * @param  int    $task_id
     * @return string
     */
    private static function getTaskUrl($project_id, $task_id)
    {
        if (empty(self::$task_url_pattern)) {
            self::$task_url_pattern = AngieApplication::getContainer()
                ->get(RouterInterface::class)
                    ->assemble(
                        'task',
                        [
                            'project_id' => '--PROJECT-ID--',
                            'task_id' => '--TASK-ID--',
                        ]
                    );
        }

        return str_replace(
            [
                '--PROJECT-ID--',
                '--TASK-ID--',
            ],
            [
                $project_id,
                $task_id,
            ],
            self::$task_url_pattern
        );
    }

    /**
     * Query calendar events changes.
     *
     * @param array     $snapshot_data
     * @param DateValue $day
     */
    private static function queryCalendarEvents(&$snapshot_data, DateValue $day)
    {
        if ($calendar_ids = DB::executeFirstColumn('SELECT id FROM calendars WHERE is_trashed = ?', false)) {
            if ($rows = DB::execute('SELECT id, calendar_id, name, starts_on, starts_on_time FROM calendar_events WHERE calendar_id IN (?) AND (repeat_until >= ? OR repeat_until is null) AND is_trashed = ? AND ' . CalendarEvents::prepareConditionsForDay($day) . ' ORDER BY id', $calendar_ids, $day, false)) {
                $rows->setCasting(['starts_on' => DBResult::CAST_DATE]);

                foreach ($rows as $row) {
                    $calendar_id = $row['calendar_id'];

                    if (empty($snapshot_data[self::TODAY]['calendar_events'][$calendar_id])) {
                        $snapshot_data[self::TODAY]['calendar_events'][$calendar_id] = [];
                    }

                    $event = [
                        'event' => self::CALENDAR_EVENT_DUE,
                        'timestamp' => $row['starts_on'] instanceof DateValue ? $row['starts_on']->toMySQL() : '',
                        'id' => $row['id'],
                        'calendar_id' => $calendar_id,
                        'name' => $row['name'],
                        'permalink' => self::getClalendarEventUrl($calendar_id, $row['id']),
                    ];

                    if ($event['timestamp'] && $row['starts_on_time']) {
                        $event['time'] = DateTimeValue::makeFromString($row['starts_on'] . ' ' . $row['starts_on_time']);
                    } else {
                        $event['time'] = null;
                    }

                    $snapshot_data[self::TODAY]['calendar_events'][$calendar_id][] = $event;
                }
            }
        }
    }

    /**
     * Query availability records.
     *
     * @param array     $snapshot_data
     * @param DateValue $day
     */
    private static function queryAvailabilityRecords(&$snapshot_data, DateValue $day)
    {
        $availability_types_id = DB::executeFirstColumn('SELECT id FROM availability_types WHERE level = ?', AvailabilityTypeInterface::LEVEL_NOT_AVAILABLE);
        $availability_types = DB::execute('SELECT id, name, level FROM availability_types WHERE id IN (?)', $availability_types_id);

        $availability_types = array_column($availability_types->toArray(), null, 'id');

        if ($rows = DB::execute('SELECT id, user_id, availability_type_id, start_date, end_date FROM availability_records WHERE start_date <= ? AND end_date >= ? AND availability_type_id IN (?)', $day, $day, $availability_types_id)) {
            $rows->setCasting([
                'id' => DBResult::CAST_INT,
                'user_id' => DBResult::CAST_INT,
                'availability_type_id' => DBResult::CAST_INT,
                'start_date' => DBResult::CAST_DATE,
                'end_date' => DBResult::CAST_DATE,
            ]);

            foreach ($rows as $row) {
                $availability_type = $availability_types[$row['availability_type_id']];

                $availability_type_name = $availability_type ? $availability_type['name'] : 'Unknown';

                $user_id = $row['user_id'];

                if (empty($snapshot_data[self::TODAY]['availability_records'][$user_id])) {
                    $snapshot_data[self::TODAY]['availability_records'][$user_id] = [];
                }

                $event = [
                    'event' => self::AVAILABILITY_RECORD_DUE,
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'id' => $row['id'],
                    'availability_type_name' => $availability_type_name,
                    'user_id' => $user_id,
                ];

                $snapshot_data[self::TODAY]['availability_records'][$user_id][] = $event;
            }
        }
    }

    /**
     * Return calendar event URL.
     *
     * @param  int    $calendar_id
     * @param  int    $event_id
     * @return string
     */
    private static function getClalendarEventUrl($calendar_id, $event_id)
    {
        if (empty(self::$calendar_event_url_pattern)) {
            self::$calendar_event_url_pattern = AngieApplication::getContainer()
                ->get(RouterInterface::class)
                    ->assemble(
                        'calendar_event',
                        [
                            'calendar_id' => '--CALENDAR-ID--',
                            'calendar_event_id' => '--EVENT-ID--',
                        ]
                    );
        }

        return str_replace(
            [
                '--CALENDAR-ID--',
                '--EVENT-ID--',
            ],
            [
                $calendar_id,
                $event_id,
            ],
            self::$calendar_event_url_pattern
        );
    }

    /**
     * Query tasks that were created or completed the previous day.
     *
     * @param array         $snapshot_data
     * @param DateTimeValue $from
     * @param DateTimeValue $to
     * @param string        $escaped_project_ids
     */
    private static function queryPrevBusinessDayTasks(&$snapshot_data, DateTimeValue $from, DateTimeValue $to, $escaped_project_ids)
    {
        // Lets get tasks that are completed previous business day
        if ($rows = DB::execute("SELECT id, project_id, name, completed_on, task_number, completed_by_id AS 'action_by_id', completed_by_name AS 'action_by_name', completed_by_email AS 'action_by_email', completed_on, start_on, due_on FROM tasks WHERE project_id IN ($escaped_project_ids) AND is_trashed = ? AND completed_on BETWEEN ? AND ? ORDER BY completed_on, id", false, $from, $to)) {
            $rows->setCasting(['completed_on' => DBResult::CAST_DATETIME]);

            foreach ($rows as $row) {
                self::logProjectEvent($snapshot_data, self::TASK_COMPLETED, $row['completed_on'], self::taskRowToEventDetails($row), $row['action_by_id'], $row['action_by_name'], $row['action_by_email']);
            }
        }

        // Lets get tasks that are created previous business day
        if ($rows = DB::execute("SELECT id, project_id, name, task_number, created_on, created_by_id AS 'action_by_id', created_by_name AS 'action_by_name', created_by_email AS 'action_by_email', completed_on, start_on, due_on FROM tasks WHERE project_id IN ($escaped_project_ids) AND is_trashed = ? AND created_on BETWEEN ? AND ? ORDER BY created_on, id", false, $from, $to)) {
            $rows->setCasting(['created_on' => DBResult::CAST_DATETIME]);

            foreach ($rows as $row) {
                self::logProjectEvent($snapshot_data, self::TASK_CREATED, $row['created_on'], self::taskRowToEventDetails($row), $row['action_by_id'], $row['action_by_name'], $row['action_by_email']);
            }
        }
    }

    /**
     * @param  array $row
     * @return array
     */
    private static function taskRowToEventDetails($row)
    {
        return [
            'id' => $row['id'],
            'project_id' => $row['project_id'],
            'name' => $row['name'],
            'task_number' => $row['task_number'],
            'permalink' => self::getTaskUrl($row['project_id'], $row['id']),
            'assignee_id' => empty($row['assignee_id']) ? 0 : $row['assignee_id'],
            'diff' => empty($row['diff']) ? 0 : $row['diff'],
            'is_completed' => (bool) $row['completed_on'],
            'start_on' => $row['start_on'],
            'due_on' => $row['due_on'],
        ];
    }

    /**
     * Query discussions and objects that were discussed the previous day.
     *
     * @param array         $snapshot_data
     * @param DateTimeValue $from
     * @param DateTimeValue $to
     * @param string        $escaped_project_ids
     */
    private static function queryPrevBusinessDayDiscussions(&$snapshot_data, DateTimeValue $from, DateTimeValue $to, $escaped_project_ids)
    {
        $new_discussion_ids = [];

        // Lets get discussions that are created previous business day
        if ($rows = DB::execute("SELECT id, project_id, name, created_on, created_by_id AS 'action_by_id', created_by_name AS 'action_by_name', created_by_email AS 'action_by_email' FROM discussions WHERE project_id IN ($escaped_project_ids) AND is_trashed = ? AND created_on BETWEEN ? AND ? ORDER BY created_on, id", false, $from, $to)) {
            $rows->setCasting(['created_on' => DBResult::CAST_DATETIME]);

            foreach ($rows as $row) {
                $new_discussion_ids[] = $row['id'];

                self::logProjectEvent($snapshot_data, self::OBJECT_DISCUSSED, $row['created_on'], [
                    'id' => $row['id'],
                    'project_id' => $row['project_id'],
                    'name' => $row['name'],
                    'permalink' => self::getDiscussionUrl($row['project_id'], $row['id']),
                ], $row['action_by_id'], $row['action_by_name'], $row['action_by_email']);
            }
        }

        if ($rows = DB::execute("SELECT parent_type, parent_id, created_on, created_by_id AS 'action_by_id', created_by_name AS 'action_by_name', created_by_email AS 'action_by_email' FROM comments AS c WHERE created_on = (SELECT MAX(created_on) FROM comments AS cc WHERE c.parent_type = cc.parent_type AND c.parent_id = cc.parent_id) AND parent_type IN ('Discussion', 'File', 'Note', 'Task') AND is_trashed = ? AND created_on BETWEEN ? AND ?", false, $from, $to)) {
            $rows->setCasting(['created_on' => DBResult::CAST_DATETIME]);

            $type_id_details_map = [];

            foreach ($rows as $row) {
                if (empty($type_id_details_map[$row['parent_type']])) {
                    $type_id_details_map[$row['parent_type']] = [];
                }

                $type_id_details_map[$row['parent_type']][$row['parent_id']] = [
                    'action_on' => $row['created_on'],
                    'action_by_id' => $row['action_by_id'],
                    'action_by_name' => $row['action_by_name'],
                    'action_by_email' => $row['action_by_email'],
                ];
            }

            foreach ($type_id_details_map as $type => $id_details) {
                switch ($type) {
                    case 'Discussion':
                        if (count($new_discussion_ids)) {
                            $rows = DB::execute("SELECT id, 'Discussion' AS 'type', project_id, name FROM discussions WHERE id IN (?) AND id NOT IN (?) AND project_id IN ($escaped_project_ids) AND is_trashed = ? ORDER BY last_comment_on", array_keys($id_details), $new_discussion_ids, false);
                        } else {
                            $rows = DB::execute("SELECT id, 'Discussion' AS 'type', project_id, name FROM discussions WHERE id IN (?) AND project_id IN ($escaped_project_ids) AND is_trashed = ? ORDER BY last_comment_on", array_keys($id_details), false);
                        }

                        break;
                    case 'File':
                        $rows = DB::execute("SELECT id, 'File' AS 'type', project_id, name FROM files WHERE id IN (?) AND project_id IN ($escaped_project_ids) AND is_trashed = ? ORDER BY last_comment_on", array_keys($id_details), false);
                        break;
                    case 'Note':
                        $rows = DB::execute("SELECT id, 'Note' AS 'type', project_id, name FROM notes WHERE id IN (?) AND project_id IN ($escaped_project_ids) AND is_trashed = ? ORDER BY last_comment_on", array_keys($id_details), false);
                        break;
                    case 'Task':
                        $rows = DB::execute("SELECT id, 'Task' AS 'type', project_id, name FROM tasks WHERE id IN (?) AND project_id IN ($escaped_project_ids) AND is_trashed = ? ORDER BY last_comment_on", array_keys($id_details), false);
                        break;
                    default:
                        $rows = null;
                }

                if ($rows) {
                    foreach ($rows as $row) {
                        $type = $row['type'];
                        $id = $row['id'];

                        switch ($type) {
                            case 'Discussion':
                                $url = self::getDiscussionUrl($row['project_id'], $id);
                                break;
                            case 'File':
                                $url = self::getFileUrl($row['project_id'], $id);
                                break;
                            case 'Note':
                                $url = self::getNoteUrl($row['project_id'], $id);
                                break;
                            case 'Task':
                                $url = self::getTaskUrl($row['project_id'], $id);
                                break;
                            default:
                                $url = '#';
                        }

                        self::logProjectEvent($snapshot_data, self::OBJECT_DISCUSSED, $type_id_details_map[$type][$id]['action_on'], [
                            'id' => $row['id'],
                            'project_id' => $row['project_id'],
                            'name' => $row['name'],
                            'permalink' => $url,
                        ], $type_id_details_map[$type][$id]['action_by_id'], $type_id_details_map[$type][$id]['action_by_name'], $type_id_details_map[$type][$id]['action_by_email']);
                    }
                }
            }
        }
    }

    /**
     * Return discussion URL.
     *
     * @param  int    $project_id
     * @param  int    $discussion_id
     * @return string
     */
    private static function getDiscussionUrl($project_id, $discussion_id)
    {
        if (empty(self::$discussion_url_pattern)) {
            self::$discussion_url_pattern = AngieApplication::getContainer()
                ->get(RouterInterface::class)
                    ->assemble(
                        'discussion',
                        [
                            'project_id' => '--PROJECT-ID--',
                            'discussion_id' => '--DISCUSSION-ID--',
                        ]
                    );
        }

        return str_replace(
            [
                '--PROJECT-ID--',
                '--DISCUSSION-ID--',
            ],
            [
                $project_id,
                $discussion_id,
            ],
            self::$discussion_url_pattern
        );
    }

    /**
     * Return file URL.
     *
     * @param  int    $project_id
     * @param  int    $file_id
     * @return string
     */
    private static function getFileUrl($project_id, $file_id)
    {
        if (empty(self::$file_url_pattern)) {
            self::$file_url_pattern = AngieApplication::getContainer()
                ->get(RouterInterface::class)
                    ->assemble(
                        'file',
                        [
                            'project_id' => '--PROJECT-ID--',
                            'file_id' => '--FILE-ID--',
                        ]
                    );
        }

        return str_replace(
            [
                '--PROJECT-ID--',
                '--FILE-ID--',
            ],
            [
                $project_id,
                $file_id,
            ],
            self::$file_url_pattern
        );
    }

    /**
     * Return note URL.
     *
     * @param  int    $project_id
     * @param  int    $note_id
     * @return string
     */
    private static function getNoteUrl($project_id, $note_id)
    {
        if (empty(self::$note_url_pattern)) {
            self::$note_url_pattern = AngieApplication::getContainer()
                ->get(RouterInterface::class)
                    ->assemble(
                        'note',
                        [
                            'project_id' => '--PROJECT-ID--',
                            'note_id' => '--FILE-ID--',
                        ]
                    );
        }

        return str_replace(
            [
                '--PROJECT-ID--',
                '--FILE-ID--',
            ],
            [
                $project_id,
                $note_id,
            ],
            self::$note_url_pattern
        );
    }

    /**
     * Query notes that were created the previous day.
     *
     * @param array         $snapshot_data
     * @param DateTimeValue $from
     * @param DateTimeValue $to
     * @param string        $escaped_project_ids
     */
    private static function queryPrevBusinessDayNotes(&$snapshot_data, DateTimeValue $from, DateTimeValue $to, $escaped_project_ids)
    {
        // Lets get discussions that are created previous business day
        if ($rows = DB::execute("SELECT id, project_id, name, created_on, created_by_id AS 'action_by_id', created_by_name AS 'action_by_name', created_by_email AS 'action_by_email' FROM notes WHERE project_id IN ($escaped_project_ids) AND is_trashed = ? AND created_on BETWEEN ? AND ? ORDER BY created_on, id", false, $from, $to)) {
            $rows->setCasting(['created_on' => DBResult::CAST_DATETIME]);

            foreach ($rows as $row) {
                self::logProjectEvent($snapshot_data, self::NOTE_CREATED, $row['created_on'], [
                    'id' => $row['id'],
                    'project_id' => $row['project_id'],
                    'name' => $row['name'],
                    'permalink' => self::getNoteUrl($row['project_id'], $row['id']),
                ], $row['action_by_id'], $row['action_by_name'], $row['action_by_email']);
            }
        }
    }

    /**
     * Query notes that were created the previous day.
     *
     * @param array         $snapshot_data
     * @param DateTimeValue $from
     * @param DateTimeValue $to
     * @param string        $escaped_project_ids
     */
    private static function queryPrevBusinessDayFiles(&$snapshot_data, DateTimeValue $from, DateTimeValue $to, $escaped_project_ids)
    {
        // Lets get discussions that are created previous business day
        if ($rows = DB::execute("SELECT id, project_id, name, created_on, created_by_id AS 'action_by_id', created_by_name AS 'action_by_name', created_by_email AS 'action_by_email' FROM files WHERE project_id IN ($escaped_project_ids) AND is_trashed = ? AND created_on BETWEEN ? AND ? ORDER BY created_on, id", false, $from, $to)) {
            $rows->setCasting(['created_on' => DBResult::CAST_DATETIME]);

            foreach ($rows as $row) {
                self::logProjectEvent($snapshot_data, self::FILE_UPLOADED, $row['created_on'], [
                    'id' => $row['id'],
                    'project_id' => $row['project_id'],
                    'name' => $row['name'],
                    'permalink' => self::getFileUrl($row['project_id'], $row['id']),
                ], $row['action_by_id'], $row['action_by_name'], $row['action_by_email']);
            }
        }
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Return unsubscribe code for $user.
     *
     * @param  User   $user
     * @return string
     */
    public static function getSubscriptionCode(User $user)
    {
        $code = $user->getAdditionalProperty('subscription_code');

        if (empty($code) || strlen($code) != 10) {
            $code = strtoupper(make_string(10));

            $user->setAdditionalProperty('subscription_code', $code);
            $user->save();
        }

        return 'MRNGPPR-' . $user->getId() . '-' . $code;
    }

    /**
     * Return true if $user can receive morning paper.
     *
     * @param  User $user
     * @return bool
     */
    public static function canReceiveMorningPaper(User $user)
    {
        return !($user instanceof Client);
    }
}
