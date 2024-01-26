<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Abstract assignments as calendar events collection.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
class AssignmentsAsCalendarEventsCollection extends CompositeCollection
{
    use IWhosAsking;

    /**
     * @var User
     */
    private $assignee;

    /**
     * @var DateValue
     */
    private $from_date;

    /**
     * @var DateValue
     */
    private $to_date;

    /**
     * Cached tag value.
     *
     * @var string
     */
    private $tag = false;

    /**
     * @var ModelCollection
     */
    private $task_lists_collection;
    private $tasks_collection;

    /**
     * Set assignee.
     *
     * @param  User              $assignee
     * @return $this
     * @throws InvalidParamError
     */
    public function &setAssignee(User $assignee)
    {
        if ($assignee instanceof User) {
            $this->assignee = $assignee;
        } else {
            throw new InvalidParamError('assignee', $assignee, 'User');
        }

        return $this;
    }

    /**
     * Set filter date from.
     *
     * @param  DateValue            $date
     * @return $this
     * @throws InvalidInstanceError
     */
    public function &setFromDate(DateValue $date)
    {
        if ($date instanceof DateValue) {
            $this->from_date = $date;
        } else {
            throw new InvalidInstanceError('from', $date, 'DateValue');
        }

        return $this;
    }

    /**
     * Set filter date to.
     *
     * @param  DateValue            $date
     * @return $this
     * @throws InvalidInstanceError
     */
    public function &setToDate(DateValue $date)
    {
        if ($date instanceof DateValue) {
            $this->to_date = $date;
        } else {
            throw new InvalidInstanceError('to', $date, 'DateValue');
        }

        return $this;
    }

    /**
     * Return collection etag.
     *
     * @param  IUser  $user
     * @param  bool   $use_cache
     * @return string
     */
    public function getTag(IUser $user, $use_cache = true)
    {
        if ($this->tag === false || empty($use_cache)) {
            $this->tag = $this->prepareTagFromBits($user->getEmail(), $this->getTimestampHash());
        }

        return $this->tag;
    }

    /**
     * Return timestamp hash.
     *
     * @return string
     */
    public function getTimestampHash()
    {
        return sha1(implode(',', [
            $this->getContextTimestamp(),
            $this->getTaskListsCollections()->getTimestampHash('updated_on'),
            $this->getTasksCollections()->getTimestampHash('updated_on'),
            $this->getRecurringTasksCollections()->getTimestampHash('updated_on'),
        ]));
    }

    /**
     * Return user or team timestamp.
     *
     * @return string
     */
    public function getContextTimestamp()
    {
        return $this->getWhosAsking()->getUpdatedOn()->toMySQL();
    }

    /**
     * Return assigned task lists collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getTaskListsCollections()
    {
        if (empty($this->task_lists_collection)) {
            if (!($this->getWhosAsking() instanceof User)) {
                throw new ImpossibleCollectionError("Invalid who's asking instance");
            }

            if (!($this->from_date instanceof DateValue) || !($this->from_date instanceof DateValue)) {
                throw new ImpossibleCollectionError('Invalid date range');
            }

            if ($this->assignee instanceof User) {
                $this->task_lists_collection = TaskLists::prepareCollection($this->getName() . '_' . $this->assignee->getId() . '_' . $this->from_date->toMySQL() . '_' . $this->to_date->toMySQL(), $this->getWhosAsking());
            } else {
                $this->task_lists_collection = TaskLists::prepareCollection($this->getName() . '_' . $this->from_date->toMySQL() . '_' . $this->to_date->toMySQL(), $this->getWhosAsking());
            }
        }

        return $this->task_lists_collection;
    }

    /**
     * Return assigned tasks collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getTasksCollections()
    {
        if (empty($this->tasks_collection)) {
            if (!($this->getWhosAsking() instanceof User)) {
                throw new ImpossibleCollectionError("Invalid who's asking instance");
            }

            if (!($this->from_date instanceof DateValue) || !($this->from_date instanceof DateValue)) {
                throw new ImpossibleCollectionError('Invalid date range');
            }

            if ($this->assignee instanceof User) {
                $this->tasks_collection = Tasks::prepareCollection($this->getName() . '_' . $this->assignee->getId() . '_' . $this->from_date->toMySQL() . '_' . $this->to_date->toMySQL(), $this->getWhosAsking());
            } else {
                $this->tasks_collection = Tasks::prepareCollection($this->getName() . '_' . $this->from_date->toMySQL() . '_' . $this->to_date->toMySQL(), $this->getWhosAsking());
            }
        }

        return $this->tasks_collection;
    }

    /**
     * @var ModelCollection
     */
    private $recurring_tasks_collection;

    /**
     * Return assigned recurring tasks collection.
     *
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    protected function &getRecurringTasksCollections()
    {
        if (empty($this->recurring_tasks_collection)) {
            if (!($this->getWhosAsking() instanceof User)) {
                throw new ImpossibleCollectionError("Invalid who's asking instance");
            }

            if (!($this->from_date instanceof DateValue) || !($this->from_date instanceof DateValue)) {
                throw new ImpossibleCollectionError('Invalid date range');
            }

            if ($this->assignee instanceof User) {
                $this->recurring_tasks_collection = RecurringTasks::prepareCollection($this->getName() . '_' . $this->assignee->getId(), $this->getWhosAsking());
            } else {
                $this->recurring_tasks_collection = RecurringTasks::prepareCollection($this->getName(), $this->getWhosAsking());
            }
        }

        return $this->recurring_tasks_collection;
    }

    /**
     * @var array
     */
    private $recurring_tasks_for_calendar = false;

    /**
     * Return recurring tasks for calendar (ghost tasks based on recurring tasks).
     *
     * @return array|bool
     */
    public function getRecurringTasksForCalendar()
    {
        if ($this->recurring_tasks_for_calendar === false) {
            try {
                $this->recurring_tasks_for_calendar = RecurringTasks::getRangeForCalendar(
                    $this->getRecurringTasksCollections()->executeIds(),
                    $this->from_date,
                    $this->to_date
                );
            } catch (ImpossibleCollectionError $e) {
                AngieApplication::log()->notice(
                    'Impossible collection: ' . $e->getMessage(),
                    [
                        'collection_name' => $this->getName(),
                        'exception' => $e,
                    ]
                );
            }

            if (empty($this->recurring_tasks_for_calendar)) {
                $this->recurring_tasks_for_calendar = [];
            }
        }

        return $this->recurring_tasks_for_calendar;
    }

    /**
     * Run the query and return DB result.
     *
     * @return DbResult|DataObject[]
     */
    public function execute()
    {
        return [
            'task_lists' => $this->getTaskListsCollections()->execute(),
            'tasks' => $this->getTasksCollections()->execute(),
            'recurring_tasks' => $this->getRecurringTasksForCalendar(),
        ];
    }

    /**
     * Return number of records that match conditions set by the collection.
     *
     * @return int
     */
    public function count()
    {
        return $this->getTaskListsCollections()->count() + $this->getTasksCollections()->count() + count($this->getRecurringTasksForCalendar());
    }

    /**
     * Return model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return 'CalendarEvents';
    }
}
