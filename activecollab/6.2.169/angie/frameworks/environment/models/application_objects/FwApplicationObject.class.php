<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Inflector;

abstract class FwApplicationObject extends DataObject
{
    /**
     * List of rich text fields.
     *
     * @var array
     */
    protected $rich_text_fields = null;

    /**
     * @var string
     */
    private $type_name_singular = false;
    private $type_name_plural = false;

    protected function __configure(): void
    {
        $traits = DataManager::getTraitNamesByObject($this);

        if ($traits) {
            foreach ($traits as $trait) {
                $trait_constructor = strpos($trait, '\\') === false ? $trait : str_replace('\\', '', $trait);

                if (method_exists($this, $trait_constructor)) {
                    $this->$trait_constructor();
                }
            }
        }

        parent::__configure();
    }

    /**
     * Return base type name.
     *
     * @param  bool   $singular
     * @return string
     */
    public function getBaseTypeName($singular = true)
    {
        if ($singular) {
            if ($this->type_name_singular === false) {
                $this->type_name_singular = Inflector::underscore(get_class($this));
            }

            return $this->type_name_singular;
        } else {
            if ($this->type_name_plural === false) {
                $this->type_name_plural = Inflector::underscore(Inflector::pluralize(get_class($this)));
            }

            return $this->type_name_plural;
        }
    }

    // ---------------------------------------------------
    //  Created / Updated By
    // ---------------------------------------------------

    /**
     * Return proper type name in user's language.
     *
     * @param  bool     $lowercase
     * @param  Language $language
     * @return string
     */
    public function getVerboseType($lowercase = false, $language = null)
    {
        return $lowercase ?
            lang(strtolower(get_class($this)), null, true, $language) :
            lang(Inflector::humanize(strtolower(get_class($this))), null, true, $language);
    }

    /**
     * Return user who last updated this object.
     *
     * @return IUser
     */
    public function getUpdatedBy()
    {
        return $this->getUserFromFieldSet('updated_by');
    }

    /**
     * Returns user instance (or NULL) for given field set.
     *
     * @param  string           $field_set_prefix
     * @return IUser|DataObject
     */
    public function getUserFromFieldSet($field_set_prefix)
    {
        $by_id = $this->getFieldValue("{$field_set_prefix}_id");
        $by_name = $this->getFieldValue("{$field_set_prefix}_name");
        $by_email = $this->getFieldValue("{$field_set_prefix}_email");

        return DataObjectPool::get(
            User::class,
            $by_id,
            function () use ($by_name, $by_email) {
                return $by_name && $by_email ? new AnonymousUser($by_name, $by_email) : null;
            }
        );
    }

    /**
     * Set person who updated this object.
     *
     * $updated_by can be an insance of User or AnonymousUser class or null
     *
     * @param  IUser|null $updated_by
     * @return IUser|null
     */
    public function setUpdatedBy($updated_by)
    {
        return $this->setUserFromFieldSet($updated_by, 'updated_by');
    }

    // ---------------------------------------------------
    //  Delegates
    // ---------------------------------------------------

    /**
     * Set by user for given field set.
     *
     * @param  IUser                   $by_user
     * @param  string                  $field_set_prefix
     * @param  bool                    $optional
     * @param  bool                    $can_be_anonymous
     * @return User|AnonymousUser|null
     */
    public function setUserFromFieldSet($by_user, $field_set_prefix, $optional = true, $can_be_anonymous = true)
    {
        if ($by_user instanceof IUser) {
            if ($by_user instanceof AnonymousUser && !$can_be_anonymous) {
                throw new InvalidInstanceError('by_user', $by_user, 'User');
            }

            $this->setFieldValue("{$field_set_prefix}_id", $by_user->getId());
            $this->setFieldValue("{$field_set_prefix}_email", $by_user->getEmail());
            $this->setFieldValue("{$field_set_prefix}_name", $by_user->getName());
        } elseif ($by_user === null) {
            if ($optional) {
                $this->setFieldValue("{$field_set_prefix}_id", 0);
                $this->setFieldValue("{$field_set_prefix}_email", '');
                $this->setFieldValue("{$field_set_prefix}_name", '');
            } else {
                throw new InvalidInstanceError('by_user', $by_user, 'IUser');
            }
        } else {
            throw new InvalidInstanceError('by_user', $by_user, 'IUser');
        }

        return $by_user;
    }

    /**
     * Returns true if $user can view this object.
     *
     * @return bool
     */
    public function canView(User $user)
    {
        return $this instanceof IChild && $this->getParent() instanceof ApplicationObject ? $this->getParent()->canView($user) : false;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can delete or move to trash this object.
     *
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $this instanceof IChild && $this->getParent() instanceof ApplicationObject ? $this->getParent()->canEdit($user) : false;
    }

    /**
     * Returns true if $user can update this object.
     *
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $this instanceof IChild && $this->getParent() instanceof ApplicationObject ? $this->getParent()->canEdit($user) : false;
    }

    // ---------------------------------------------------
    //  URL-s
    // ---------------------------------------------------

    /**
     * Return object path.
     *
     * @return string
     */
    public function getObjectPath()
    {
        return $this->isLoaded() ? str_replace('_', '-', $this->getModelName(true, false)) . '/' . $this->getId() : '';
    }

    /**
     * Checks if object is accessible.
     *
     * @return bool
     */
    public function isAccessible()
    {
        if ($this->isLoaded()) {
            return $this instanceof ITrash ? !$this->getIsTrashed() : true;
        }

        return false;
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Save application object properties to the database.
     *
     * @return bool
     */
    public function save()
    {
        $is_new = $this->isNew();

        try {
            DB::beginWork('Saving application object @ ' . __CLASS__);

            $modifications = [];

            if (count($this->getModifiedFields())) {
                foreach ($this->getModifiedFields() as $field) {
                    $old_value = $this->getOldFieldValue($field);
                    $new_value = $this->getFieldValue($field);

                    if ($old_value != $new_value) {
                        $modifications[$field] = [$this->getOldFieldValue($field), $this->getFieldValue($field)];
                    }
                }
            }

            if ($is_new && $this->fieldExists('type') && $this->getFieldValue('type') == '') {
                $this->setFieldValue('type', get_class($this));
            }

            $this->triggerEvent('on_before_save', [$is_new, $modifications]); // Before application object save

            parent::save();

            // Auto-subscribe mentioned users
            if ($this instanceof ISubscriptions && $this instanceof IBody) {
                $mentioned_users = !empty($this->getNewMentions()) ? Users::find([
                    'conditions' => ['id IN (?) AND is_trashed = ? AND is_archived = ?', $this->getNewMentions(), false, false],
                ]) : null;

                if ($mentioned_users) {
                    foreach ($mentioned_users as $mentioned_user) {
                        if (ConfigOptions::getValueFor('subscribe_on_mention', $mentioned_user)) {
                            $this->subscribe($mentioned_user);
                        }
                    }
                }
            }

            $this->triggerEvent('on_after_save', [$is_new, $modifications]); // After application object save

            if (isset($modifications) && $modifications) {
                if (isset($attachments_modification) && $attachments_modification) {
                    $modifications['attachments'] = $attachments_modification;
                }

                if (isset($assignees_modification) && $assignees_modification) {
                    $modifications['assignees'] = $assignees_modification;
                }
            }

            DB::commit('Application object saved @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to save application object @ ' . __CLASS__);
            throw $e;
        }

        return true;
    }
}
