<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Basic trash interface implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
trait ITrashImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function ITrashImplementation()
    {
        if ($this instanceof IHistory) {
            $this->addHistoryFields('is_trashed');
        }

        $this->registerEventHandler('on_history_field_renderers', function (&$renderers) {
            $renderers['is_trashed'] = function ($old_value, $new_value, Language $language) {
                if ($new_value) {
                    return lang('Moved to trash', null, true, $language);
                } else {
                    return lang('Restored from trash', null, true, $language);
                }
            };
        });

        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['is_trashed'] = $this->getIsTrashed();
            $result['trashed_on'] = $this->getTrashedOn();
            $result['trashed_by_id'] = $this->getTrashedById();
        });
    }

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);

    /**
     * Return value of is_trashed field.
     *
     * @return bool
     */
    abstract public function getIsTrashed();

    /**
     * Return value of trashed_on field.
     *
     * @return DateTimeValue
     */
    abstract public function getTrashedOn();

    /**
     * Get value of trashed_by_id field.
     *
     * @return int
     */
    abstract public function getTrashedById();

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Move to trash.
     *
     * @param User $by
     * @param bool $bulk
     */
    public function moveToTrash(User $by = null, $bulk = false)
    {
        DB::transact(function () use ($by, $bulk) {
            $this->triggerEvent('on_before_move_to_trash', [&$by, $bulk]);

            if ($bulk && method_exists($this, 'setOriginalIsTrashed')) {
                $this->setOriginalIsTrashed($this->getIsTrashed());
            }

            $this->setIsTrashed(true);
            $this->setTrashedOn(DateTimeValue::now());

            if ($by instanceof User) {
                $this->setTrashedById($by->getId());
            } else {
                $this->setTrashedById(AngieApplication::authentication()->getLoggedUserId());
            }

            $this->save();

            if (empty($bulk) && $this instanceof IChild) {
                $this->getParent()->touch();
            }

            $this->triggerEvent('on_after_move_to_trash', [$bulk]);

            if (!$bulk) {
                Angie\Events::trigger('on_moved_to_trash', [&$this]);
            }
        }, 'Moving object to trash');
    }

    /**
     * Trigger an internal event.
     *
     * @param string $event
     * @param array  $event_parameters
     */
    abstract protected function triggerEvent($event, $event_parameters = null);

    /**
     * Set value of is_trashed field.
     *
     * @param  bool $value
     * @return bool
     */
    abstract public function setIsTrashed($value);

    /**
     * Set value of trashed_on field.
     *
     * @param  DateTimeValue $value
     * @return DateTimeValue
     */
    abstract public function setTrashedOn($value);

    /**
     * Set value of trashed_by_id field.
     *
     * @param  int $value
     * @return int
     */
    abstract public function setTrashedById($value);

    /**
     * Save updates to database.
     */
    abstract public function save();

    /**
     * Restore from trash.
     *
     * @param bool $bulk
     */
    public function restoreFromTrash($bulk = false)
    {
        if ($this->getIsTrashed()) {
            DB::transact(function () use ($bulk) {
                $this->triggerEvent('on_before_restore_from_trash', [$bulk]);

                if ($bulk && method_exists($this, 'getOriginalIsTrashed') && method_exists($this, 'setOriginalIsTrashed')) {
                    $this->setIsTrashed($this->getOriginalIsTrashed());
                    $this->setOriginalIsTrashed(false);
                } else {
                    $this->setIsTrashed(false);
                }

                $this->setTrashedOn(null);
                $this->setTrashedById(0);
                $this->save();

                if (empty($bulk) && $this instanceof IChild) {
                    $this->getParent()->touch();
                }

                $this->triggerEvent('on_after_restore_from_trash', [$bulk]);

                if (!$bulk) {
                    Angie\Events::trigger('on_restored_from_trash', [&$this]);
                }
            }, 'Moving object to trash');
        }
    }

    /**
     * Return true if $user can move this object to trash.
     *
     * @param  User $user
     * @return bool
     */
    public function canTrash(User $user)
    {
        return $this->canEdit($user);
    }

    /**
     * @param  User $user
     * @return bool
     */
    abstract public function canEdit(User $user);

    /**
     * Return true if $user can restore this object from trash.
     *
     * @param  User $user
     * @return bool
     */
    public function canRestoreFromTrash(User $user)
    {
        if ($this->getIsTrashed()) {
            if ($this instanceof IChild) {
                $parent = $this->getParent();

                if ($parent instanceof ITrash && $parent->getIsTrashed()) {
                    return false;
                }
            }

            return $user->isOwner() || $this->getTrashedById() == $user->getId();
        }

        return false;
    }
}
