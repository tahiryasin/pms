<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level calendar event implementation.
 *
 * @package angie.frameworks.calendars
 * @subpackage models
 */
abstract class FwCalendarEvent extends BaseCalendarEvent implements ICalendarFeedElement
{
    use ICalendarFeedElementImplementation;

    /**
     * Repeat Values.
     */
    const DONT_REPEAT = 'dont';
    const REPEAT_DAILY = 'daily';
    const REPEAT_WEEKLY = 'weekly';
    const REPEAT_MONTHLY = 'monthly';
    const REPEAT_YEARLY = 'yearly';

    const AVAILABLE_REPEAT_VALUES = [self::DONT_REPEAT, self::REPEAT_DAILY, self::REPEAT_WEEKLY, self::REPEAT_MONTHLY, self::REPEAT_YEARLY];

    /**
     * Repeat Options.
     */
    const REPEAT_OPTION_DEFAULT = 'default';
    const REPEAT_OPTION_FOREVER = 'forever';
    const REPEAT_OPTION_PERIODIC = 'periodic';
    const REPEAT_OPTION_SELECT_DATE = 'date';

    /**
     * Construct data object and if $id is present load.
     *
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->addHistoryFields('name', 'calendar_id', 'starts_on', 'ends_on', 'starts_on_time', 'repeat_event');
    }

    /**
     * Return proper type name in user's language.
     *
     * @param  bool     $lowercase
     * @param  Language $language
     * @return string
     */
    public function getVerboseType($lowercase = false, $language = null)
    {
        return $lowercase ? lang('event', null, true, $language) : lang('Event', null, true, $language);
    }

    /**
     * Can user view event.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isOwner() || $this->isCreatedBy($user) || $this->getCalendar()->isMember($user);
    }

    /**
     * Can user edit event.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isOwner() || $this->isCreatedBy($user) || $this->getCalendar()->isCreatedBy($user);
    }

    /**
     * Can user delete event.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isOwner() || $this->isCreatedBy($user) || $this->getCalendar()->isCreatedBy($user);
    }

    /**
     * Returns true if this event is not a single day event, but spans across multiple days.
     *
     * @return bool
     */
    public function isSpan()
    {
        return $this->getEndsOn()->getTimestamp() > $this->getStartsOn()->getTimestamp();
    }

    /**
     * Returns true if this event is repeating.
     *
     * @return bool
     */
    public function isRepeating()
    {
        return $this->getRepeatEvent() !== self::DONT_REPEAT;
    }

//    /**
//     * Set repeat until.
//     *
//     * @param  DateValue       $repeat
//     * @param                  $option
//     * @param                  $option_values
//     * @return DateValue|mixed
//     */
//    public function setRepeatUntil($repeat, $option, $option_values)
//    {
//        $start_on = $this->getStartsOn();
//        if ($option == CalendarEvent::REPEAT_OPTION_PERIODIC) {
//            $pre_value = array_var($option_values, CalendarEvent::REPEAT_OPTION_PERIODIC) - 1;
//            switch ($repeat) {
//                case self::REPEAT_YEARLY:
//                    $interval_period = 'Y';
//                    break;
//                case self::REPEAT_MONTHLY:
//                    $interval_period = 'M';
//                    break;
//                case self::REPEAT_WEEKLY:
//                    $interval_period = 'W';
//                    break;
//                case self::REPEAT_DAILY:
//                    $interval_period = 'D';
//                    break;
//                default:
//                    $interval_period = null;
//                    break;
//            }

//            if ($interval_period) {
//                $interval = new DateInterval('P' . $pre_value . $interval_period);
//                $date = new DateTime($start_on->toMySQL());
//                $value = DateValue::makeFromTimestamp($date->add($interval)->getTimestamp());
//            } else {
//                $value = null;
//            }
//        } elseif ($option == CalendarEvent::REPEAT_OPTION_SELECT_DATE) {
//            $pre_value = array_var($option_values, CalendarEvent::REPEAT_OPTION_SELECT_DATE);
//            $value = DateValue::makeFromString($pre_value);
//        } elseif ($option == CalendarEvent::REPEAT_OPTION_FOREVER) {
//            $value = null;
//        } else {
//            $value = $this->getRepeatUntil();
//        }

//        return $this->setFieldValue('repeat_until', $value);
//    }

    /**
     * Return starts_on value.
     *
     * @return DateTimeValue
     */
    public function getStartsOn()
    {
        return $this->getDateWithTimeComponent(parent::getStartsOn(), $this->getStartsOnTime());
    }

    /**
     * Return ends_on value.
     *
     * @return DateTimeValue
     */
    public function getEndsOn()
    {
        return $this->getDateWithTimeComponent(parent::getEndsOn(), $this->getEndsOnTime());
    }

    /**
     * Return date with time component.
     *
     * @param  DateValue     $date
     * @return DateTimeValue
     */
    protected function getDateWithTimeComponent(DateValue $date, $time = null)
    {
        if (!empty($time)) {
            $date = DateTimeValue::makeFromString(($date instanceof DateTimeValue ? $date->dateToMySQL() : $date->toMySQL()) . ' ' . $time);
        }

        return DateTimeValue::makeFromTimestamp($date->getTimestamp());
    }

    /**
     * Get calendar.
     *
     * @return Calendar
     */
    public function &getCalendar()
    {
        return DataObjectPool::get(UserCalendar::class, $this->getCalendarId());
    }

    /**
     * Set calendar.
     *
     * @param FwCalendar|Calendar $calendar
     */
    public function setCalendar(Calendar $calendar)
    {
        $this->setCalendarId($calendar->getId());
    }

    // ---------------------------------------------------
    //  Interface implementations
    // ---------------------------------------------------

    public function getRoutingContext(): string
    {
        return 'calendar_event';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'calendar_id' => $this->getCalendarId(),
            'calendar_event_id' => $this->getId(),
        ];
    }

    /**
     * Describe object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['calendar_id'] = $this->getCalendarId();
        $result['starts_on'] = $this->getStartsOn();
        $result['ends_on'] = $this->getEndsOn();
        $result['repeat_event'] = $this->getRepeatEvent();
        $result['repeat_until'] = $this->getRepeatUntil();
        $result['starts_on_time'] = $this->getStartsOnTime();
        $result['ends_on_time'] = $this->getEndsOnTime();
        $result['note'] = $this->getNote();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedDateStart()
    {
        $starts_on = $this->getStartsOn();

        if ($starts_on_time = $this->getStartsOnTime()) {
            return DateTimeValue::makeFromString($starts_on->dateToMySQL() . ' ' . $starts_on_time);
        }

        return DateValue::makeFromTimestamp($starts_on->getTimestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedDateEnd()
    {
        $ends_on = $this->getEndsOn();

        if ($this->getStartsOnTime()) {
            return DateTimeValue::makeFromString($ends_on->dateToMySQL() . ' ' . $this->getEndsOnTime());
        }

        return DateValue::makeFromTimestamp($ends_on->advance(86400)->getTimestamp()); // +1 day
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarFeedRepeatingRule()
    {
        if ($this->isRepeating()) {
            $repeat_rules = [];

            switch ($this->getRepeatEvent()) {
                case self::REPEAT_DAILY:
                    $freq = 'DAILY';
                    break;
                case self::REPEAT_WEEKLY:
                    $freq = 'WEEKLY';
                    break;
                case self::REPEAT_MONTHLY:
                    $freq = 'MONTHLY';
                    break;
                case self::REPEAT_YEARLY:
                    $freq = 'YEARLY';
                    break;
                default:
                    $freq = false;
                    break;
            }

            if ($freq) {
                $repeat_rules[] = "FREQ={$freq}";

                if ($repeat_until = $this->getRepeatUntil()) {
                    $repeat_rules[] = "UNTIL={$repeat_until->toICalendar()}";
                }

                return implode(';', $repeat_rules);
            }
        }

        return null;
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        $this->validatePresenceOf('calendar_id') or $errors->fieldValueIsRequired('calendar_id');
        $this->validatePresenceOf('name') or $errors->fieldValueIsRequired('name');
        $this->validatePresenceOf('starts_on') or $errors->fieldValueIsRequired('starts_on');
        $this->validatePresenceOf('ends_on') or $errors->fieldValueIsRequired('ends_on');

        if ($this->validatePresenceOf('starts_on') && $this->validatePresenceOf('ends_on')) {
            $starts_on = $this->getStartsOn();
            $ends_on = $this->getEndsOn();

            if ($starts_on instanceof DateValue && $ends_on instanceof DateValue) {
                if ($this->getStartsOnTime() && $this->getEndsOnTime()) {
                    $starts_on_timestamp = $starts_on->getTimestamp();
                    $ends_on_timestamp = $ends_on->getTimestamp();
                } else {
                    $starts_on_timestamp = $starts_on instanceof DateTimeValue
                        ? $starts_on->beginningOfDay()->getTimestamp()
                        : $starts_on->getTimestamp();

                    $ends_on_timestamp = $ends_on instanceof DateTimeValue
                        ? $ends_on->beginningOfDay()->getTimestamp()
                        : $ends_on->getTimestamp();
                }

                if ($starts_on_timestamp > $ends_on_timestamp) {
                    $errors->addError('Invalid date range', 'invalid_date_range');
                }
            } else {
                $errors->addError('Invalid start and/or end date', 'invalid_dates');
            }
        }

        parent::validate($errors);
    }

    /**
     * Move to trash.
     *
     * @param User|null $by
     * @param bool      $bulk
     */
    public function moveToTrash(User $by = null, $bulk = false)
    {
        parent::moveToTrash($by, $bulk);
        $this->getCalendar()->touch();
    }

    /**
     * Restore from trash.
     *
     * @param bool $bulk
     */
    public function restoreFromTrash($bulk = false)
    {
        parent::restoreFromTrash();
        $this->getCalendar()->touch();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($bulk = false)
    {
        parent::delete($bulk);
        $this->getCalendar()->touch();
    }

    /**
     * Save to the database.
     */
    public function save()
    {
        if (in_array($this->getRepeatEvent(), [self::DONT_REPEAT, null])) {
            $this->setRepeatUntil(null);

            if ($this->getRepeatEvent() === null) {
                $this->setRepeatEvent(self::DONT_REPEAT);
            }
        }

        parent::save();
        $this->getCalendar()->touch();
    }
}
