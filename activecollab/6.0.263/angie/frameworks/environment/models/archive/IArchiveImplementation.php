<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Basic archive interface implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage models
 */
trait IArchiveImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function IArchiveImplementation()
    {
        if ($this instanceof IHistory) {
            $this->addHistoryFields('is_archived');
        }

        $this->registerEventHandler('on_history_field_renderers', function (&$renderers) {
            $renderers['is_archived'] = function ($old_value, $new_value, Language $language) {
                if ($new_value) {
                    return lang('Moved to archive', null, true, $language);
                } else {
                    return lang('Restored from archive', null, true, $language);
                }
            };
        });

        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['is_archived'] = $this->getIsArchived();
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
     * Return true if parent object is archived.
     *
     * @return bool
     */
    abstract public function getIsArchived();

    /**
     * Move to archive.
     *
     * @param User $by
     * @param bool $bulk
     */
    public function moveToArchive(User $by, $bulk = false)
    {
        DB::transact(function () use ($by, $bulk) {
            $this->triggerEvent('on_before_move_to_archive', [$by, $bulk]);

            if ($bulk && method_exists($this, 'setOriginalIsArchived')) {
                $this->setOriginalIsArchived($this->getIsArchived());
            }

            if (method_exists($this, 'setArchivedOn')) {
                $this->setArchivedOn(DateTimeValue::now());
            }

            $this->setIsArchived(true);
            $this->save();

            $this->triggerEvent('on_after_move_to_archive', [$bulk]);
        }, 'Moving object to archive');
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Trigger an internal event.
     *
     * @param string $event
     * @param array  $event_parameters
     */
    abstract protected function triggerEvent($event, $event_parameters = null);

    /**
     * Set value of is_archived field.
     *
     * @param  bool $value
     * @return bool
     */
    abstract public function setIsArchived($value);

    /**
     * Save to database.
     */
    abstract public function save();

    /**
     * Restore from archive.
     *
     * @param bool $bulk
     */
    public function restoreFromArchive($bulk = false)
    {
        if ($this->getIsArchived()) {
            DB::transact(
                function () use ($bulk) {
                    $this->triggerEvent('on_before_restore_from_archive', [$bulk]);

                    if ($bulk
                        && method_exists($this, 'getOriginalIsArchived')
                        && method_exists($this, 'setOriginalIsArchived')
                    ) {
                        $this->setIsArchived($this->getOriginalIsArchived());
                        $this->setOriginalIsArchived(false);
                    } else {
                        $this->setIsArchived(false);
                    }

                    if (method_exists($this, 'setArchivedOn')) {
                        $this->setArchivedOn(null);
                    }

                    $this->save();

                    $this->triggerEvent('on_after_restore_from_archive', [$bulk]);
                },
                'Moving object to archive'
            );
        }
    }

    /**
     * Return true if $user can archive this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canArchive(User $user)
    {
        return $this->canEdit($user);
    }

    /**
     * @param  User $user
     * @return bool
     */
    abstract public function canEdit(User $user);
}
