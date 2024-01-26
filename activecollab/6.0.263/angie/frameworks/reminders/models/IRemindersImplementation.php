<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reminders helper implementation.
 *
 * @package angie.frameworks.reminders
 * @subpackage models
 */
trait IRemindersImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function IRemindersImplementation()
    {
        $this->registerEventHandler('on_before_delete', function () {
            if ($reminders = $this->getReminders()) {
                foreach ($reminders as $reminder) {
                    $reminder->delete(true);
                }
            }
        });

        $this->registerEventHandler('on_describe_single', function (array &$result) {
            $result['reminders'] = DB::executeFirstColumn('SELECT DISTINCT created_by_id FROM reminders WHERE parent_type = ? AND parent_id = ?', get_class($this), $this->getId());

            if (empty($result['reminders'])) {
                $result['reminders'] = [];
            }
        });
    }

    /**
     * Register an internal event handler.
     *
     * @param  string            $event
     * @param  callable          $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Return reminders.
     *
     * @return Reminder[]|null
     */
    public function getReminders()
    {
        return Reminders::find([
            'conditions' => ['parent_type = ? AND parent_id = ?', get_class($this), $this->getId()],
        ]);
    }

    /**
     * @return int
     */
    abstract public function getId();
}
