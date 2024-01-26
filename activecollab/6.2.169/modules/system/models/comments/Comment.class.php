<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Search\SearchItem\SearchItemInterface as SearchItem;

class Comment extends BaseComment
{
    const SOURCE_WEB = 'web';
    const SOURCE_EMAIL = 'email';
    const SOURCE_API = 'api';
    const SOURCE_SHARED_PAGE = 'shared_page';

    /**
     * Return comment name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getParent() instanceof IComments ?
            lang('Comment on :name', ['name' => $this->getParent()->getName()], false) :
            lang('Comment');
    }

    /**
     * Return base type name.
     *
     * @param  bool   $singular
     * @return string
     */
    public function getBaseTypeName($singular = true)
    {
        return $singular ? 'comment' : 'comments';
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
        return $lowercase ? lang('comment', $language) : lang('Comment', $language);
    }

    /**
     * Check if comment can be viewed.
     *
     * @return bool
     */
    public function isAccessible()
    {
        return true;
    }

    /**
     * Return project ID for this comment.
     *
     * Note: If this comment is not posted on a project element, or project element does not exists, 0 will be returned
     *
     * @return mixed
     */
    public function getProjectId()
    {
        return AngieApplication::cache()->getByObject($this, 'project_id', function () {
            switch ($this->getParentType()) {
                case Discussion::class:
                    $parent_table = 'discussions';
                    break;
                case File::class:
                    $parent_table = 'files';
                    break;
                case Note::class:
                    $parent_table = 'notes';
                    break;
                case Task::class:
                    $parent_table = 'tasks';
                    break;
                default:
                    $parent_table = '';
            }

            return $parent_table
                ? (int) DB::executeFirstCell(
                    sprintf(
                        'SELECT p.project_id FROM %s AS p LEFT JOIN comments AS c ON p.id = c.parent_id WHERE c.id = ?',
                        $parent_table
                    ),
                    $this->getId())
                : 0;
        });
    }

    // ---------------------------------------------------
    //  Interface implementations
    // ---------------------------------------------------

    public function getRoutingContext(): string
    {
        return 'comment';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'comment_id' => $this->getId(),
        ];
    }

    public function touchParentOnPropertyChange(): ?array
    {
        return [
            'body',
            'is_trashed',
            'updated_on',
        ];
    }

    /**
     * Include plain text version of body in the JSON response.
     *
     * @return bool
     */
    protected function includePlainTextBodyInJson()
    {
        return true;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can access this attachment.
     *
     * @return bool
     */
    public function canView(User $user)
    {
        if ($this->getIsTrashed() && $this->getTrashedById() === $user->getId()) {
            return true;
        }

        return $this->getParent() && $this->getParent()->canView($user);
    }

    /**
     * Returns true if $user can update this comment.
     *
     * Only owner and comment author in given timeframe can update comment text
     *
     * @return bool
     */
    public function canEdit(User $user)
    {
        if ($this->getIsTrashed()) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        if ((new ReflectionClass($this->getParentType()))->implementsInterface(IProjectElement::class)) {
            $project = DataObjectPool::get(Project::class, $this->getProjectId());

            if ($project instanceof Project) {
                if ($project->isLeader($user)) {
                    return true;
                }
            } else {
                return false;
            }
        }

        return $this->isCreatedBy($user)
            && ($this->getCreatedOn()->getTimestamp() + 1800) > DateTimeValue::now()->getTimestamp();
    }

    /**
     * Returns true if $user can delete this comment.
     *
     * @return bool
     */
    public function canDelete(User $user)
    {
        if ($this->getIsTrashed()) {
            return $user->isOwner() || $this->getTrashedById() === $user->getId();
        }

        return false;
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Validate before save.
     */
    public function validate(ValidationErrors &$errors)
    {
        if (!$this->validateHTML($this->getBody(), 1)) {
            $errors->addError('Comment is required', 'body');
        }

        if (!$this->validatePresenceOf('created_by_name')) {
            $errors->addError('Author name is required', 'created_by_name');
        }

        if ($this->validatePresenceOf('created_by_email')) {
            if (!is_valid_email($this->getCreatedByEmail())) {
                $errors->addError('Authors email address is not valid', 'created_by_email');
            }
        } else {
            $errors->addError('Authors email address is required', 'created_by_email');
        }
    }

    /**
     * Validate that HTML data exists in provided HTML.
     *
     * @param  string $html
     * @param  bool   $min_length
     * @return bool
     */
    private static function validateHTML($html, $min_length = false)
    {
        $html = (string) $html;

        $html = strip_tags($html, '<div><img><a>');
        $html = trim($html);

        $html_length = strlen_utf($html);

        if ($html_length) {
            if ($min_length) {
                return $html_length >= $min_length;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Save comment into database.
     *
     * @throws Exception
     */
    public function save()
    {
        $search_index_affected = $this->isSearchIndexAffected();

        $parent = $this->getParent();

        try {
            DB::beginWork('Save comment @ ' . __CLASS__);

            // Subscribe mentioned users to parent
            if ($parent instanceof ISubscriptions) {
                $mentioned_users = !empty($this->getNewMentions())
                    ? Users::find(
                        [
                            'conditions' => [
                                'id IN (?) AND is_trashed = ? AND is_archived = ?',
                                $this->getNewMentions(),
                                false,
                                false,
                            ],
                        ]
                    )
                    : null;

                if ($mentioned_users) {
                    foreach ($mentioned_users as $mentioned_user) {
                        if (ConfigOptions::getValueFor('subscribe_on_mention', $mentioned_user)) {
                            $parent->subscribe($mentioned_user);
                        }
                    }
                }
            }

            parent::save();

            DB::commit('Comment saved @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to save comment @ ' . __CLASS__);
            throw $e;
        }

        if ($search_index_affected && $parent instanceof SearchItem) {
            AngieApplication::search()->update($parent);
        }
    }

    /**
     * Return true if changes that are in this object affect parent's search index.
     *
     * @return bool
     */
    private function isSearchIndexAffected()
    {
        return $this->isNew()
            || $this->hasAttachmentUpdatesToSave()
            || $this->isModifiedField('is_trashed')
            || $this->isModifiedField('body');
    }

    // ---------------------------------------------------
    //  Activity logs
    // ---------------------------------------------------

    /**
     * Prepare and return creation log entry.
     *
     * @return ActivityLog|null
     */
    protected function getCreatedActivityLog()
    {
        $parent = $this->getParent();

        if ($parent instanceof IActivityLog) {
            $log = new CommentCreatedActivityLog();

            $log->setParent($parent);
            $log->setParentPath($parent->getObjectPath());
            $log->setComment($this);

            $created_by = $this instanceof ICreatedBy && $this->getCreatedBy() instanceof IUser
                ? $this->getCreatedBy()
                : AngieApplication::authentication()->getLoggedUser();

            if ($created_by instanceof IUser) {
                $log->setCreatedBy($created_by);
            }

            return $log;
        }

        return null;
    }

    public function clearActivityLogs(): void
    {
        $parent = $this->getParent();

        if ($parent instanceof IActivityLog) {
            ActivityLogs::deleteByParentAndAdditionalProperty(
                $parent,
                'comment_id',
                $this->getId()
            );
        } else {
            AngieApplication::log()->warning(
                "Comment {comment_id} can't be deleted by parent ID, because parent was not found.",
                [
                    'comment_id' => $this->getId(),
                ]
            );
        }
    }
}
