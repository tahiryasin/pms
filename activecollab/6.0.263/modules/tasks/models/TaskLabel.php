<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Task label implementation.
 *
 * @package ActiveCollab.modules.tasks
 * @subpackage models
 */
class TaskLabel extends Label implements TaskLabelInterface
{
    /**
     * Set if you wish to have name always uppercased for this label type.
     *
     * @var bool
     */
    protected $always_uppercase = false;

    /**
     * Return true if $user can update this task label.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return !$this->getIsDefault() && self::countProjectsByTaskLabelForUser($user->getId()) ? true : parent::canEdit($user);
    }

    /**
     * Return number of projects on which this label is used.
     *
     * @param  null              $user_id
     * @return mixed
     * @throws InvalidParamError
     */
    protected function countProjectsByTaskLabelForUser($user_id = null)
    {
        return DB::executeFirstCell('SELECT COUNT(id) FROM projects as p, (SELECT t.project_id FROM tasks as t LEFT JOIN parents_labels as l ON l.parent_id = t.id WHERE l.label_id = ? AND l.parent_type = ?) as x WHERE x.project_id = p.id AND p.leader_id = ?', $this->getId(), 'Task', $user_id);
    }

    /**
     * Return true if $user can delete this task label.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return !$this->getIsDefault() && self::countProjectsByTaskLabelForUser($user->getId()) ? true : parent::canDelete($user);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'is_global' => $this->getIsGlobal(),
        ]);
    }

    /**
     * Save to database.
     */
    public function save()
    {
        $is_new = $this->isNew();

        parent::save();

        if ($is_new) {
            Projects::clearCache();
        }
    }

    /**
     * Remove this label from database.
     *
     * @param  bool      $bulk
     * @throws Exception
     */
    public function delete($bulk = false)
    {
        parent::delete($bulk);

        if (!$bulk) {
            Tasks::clearCache();
        }
    }
}
