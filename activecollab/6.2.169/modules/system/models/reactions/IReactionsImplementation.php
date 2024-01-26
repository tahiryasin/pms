<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Implementation of IReactions interface that is attached to actual objects.
 *
 * @package angie.frameworks.reactions
 * @subpackage models
 */
trait IReactionsImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function IReactionsImplementation()
    {
        $this->registerEventHandler('on_json_serialize', function (array &$result) {
            $result['reactions'] = Reactions::getDetailsByParent($this);
        });

        $this->registerEventHandler('on_before_delete', function () {
            if ($reaction_ids = DB::execute('SELECT id FROM reactions WHERE parent_type = ? AND parent_id = ?', get_class($this), $this->getId())) {
                try {
                    DB::beginWork('Droping reactions @ ' . __CLASS__);

                    DB::execute('DELETE FROM reactions WHERE id IN (?)', $reaction_ids);

                    DB::commit('Reactions dropped @ ' . __CLASS__);
                } catch (Exception $e) {
                    DB::rollback('Failed to drop reactions @ ' . __CLASS__);
                    throw $e;
                }

                Reactions::clearCache();
            }
        });
    }

    /**
     * Return reaction submitted for this project object.
     *
     * @return DBResult|Reaction[]
     */
    public function getReactions()
    {
        return Reactions::find([
            'conditions' => ['parent_type = ? AND parent_id = ?', get_class($this), $this->getId()],
        ]);
    }

    /**
     * Return existing reaction by user.
     *
     * @param  string              $type
     * @param  int                 $created_by_id
     * @return DataObject|Reaction
     */
    public function getExistingReactionByUser($type, $created_by_id)
    {
        return Reactions::findOneBy(
            [
                'parent_type' => get_class($this),
                'parent_id' => $this->getId(),
                'type' => $type,
                'created_by_id' => $created_by_id,
            ]
        );
    }

    // ---------------------------------------------------
    //  Utility methods
    // ---------------------------------------------------

    /**
     * Quickly create and submit a reaction.
     *
     * @param  IUser     $by
     * @param  array     $additional
     * @throws Exception
     * @return Reaction
     * @throws Exception
     */
    public function submitReaction(IUser $by, $additional = null)
    {
        $attributes = is_array($additional) ? $additional : [];

        $attributes['parent_type'] = get_class($this);
        $attributes['parent_id'] = $this->getId();

        $attributes['created_by_id'] = $by->getId();
        $attributes['created_by_name'] = $by->getDisplayName();
        $attributes['created_by_email'] = $by->getEmail();

        /** @var Reaction $reaction */
        if ($reaction = Reactions::create($attributes)) {
            DataObjectPool::announce($reaction, DataObjectPool::OBJECT_CREATED);
        }

        /** @var Comment $parent */
        $parent = $reaction->getParent();

        AngieApplication::notifications()
            ->notifyAbout('new_reaction', $parent->getParent(), $by)
            ->setComment($parent)
            ->setReaction($reaction)
            ->sendToUsers([$parent->getCreatedBy()]);

        AngieApplication::log()->info(
            'Reaction added: {reaction_type}',
            [
                'reaction_type' => $reaction->getType(),
            ]
        );

        return $reaction;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if this object allows anonymous reactions.
     *
     * @return bool
     */
    public function allowAnonymousReactions()
    {
        return true;
    }

    /**
     * Returns true if $user can post a reaction to this object.
     *
     * @param  IUser                $user
     * @return bool
     * @throws InvalidInstanceError
     */
    public function canReact(IUser $user)
    {
        if ($this instanceof ITrash && $this->getIsTrashed()) {
            return false;
        }

        if ($user instanceof User) {
            return $this->canView($user);
        } elseif ($user instanceof AnonymousUser) {
            return $this->allowAnonymousReactions();
        } else {
            throw new InvalidInstanceError('user', $user, [User::class, AnonymousUser::class]);
        }
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return object ID.
     *
     * @return int
     */
    abstract public function getId();

    /**
     * Return true if $user can view this object.
     *
     * @param  User  $user
     * @return mixed
     */
    abstract public function canView(User $user);

    /**
     * Register an internal event handler.
     *
     * @param  string            $event
     * @param  callable          $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);
}
