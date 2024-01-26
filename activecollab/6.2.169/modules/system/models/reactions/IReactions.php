<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Requirements for reactable objects.
 *
 * @package angie.frameworks.reactions
 * @subpackage models
 */
interface IReactions
{
    const REACTION_SMILE = 'smile';
    const REACTION_THUMBS_UP = 'thumbs_up';
    const REACTION_THUMBS_DOWN = 'thumbs_down';
    const REACTION_THINKING = 'thinking';
    const REACTION_HEART = 'heart';
    const REACTION_PARTY = 'party';
    const REACTION_APPLAUSE = 'applause';

    const REACTION_TYPES = [
        self::REACTION_SMILE => SmileReaction::class,
        self::REACTION_THUMBS_UP => ThumbsUpReaction::class,
        self::REACTION_THUMBS_DOWN => ThumbsDownReaction::class,
        self::REACTION_THINKING => ThinkingReaction::class,
        self::REACTION_HEART => HeartReaction::class,
        self::REACTION_PARTY => PartyReaction::class,
        self::REACTION_APPLAUSE => ApplauseReaction::class,
    ];

    /**
     * Return reactions submitted for this project object.
     *
     * @return Reaction[]
     */
    public function getReactions();

    /**
     * Return existing reaction by user.
     *
     * @param  string              $type
     * @param  int                 $created_by_id
     * @return DataObject|Reaction
     */
    public function getExistingReactionByUser($type, $created_by_id);

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
    public function submitReaction(IUser $by, $additional = null);

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if this object allows anonymous reactions.
     *
     * @return bool
     */
    public function allowAnonymousReactions();

    /**
     * Returns true if $user can leave reaction to this object.
     *
     * @param  IUser $user
     * @return bool
     */
    public function canReact(IUser $user);

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Return object ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Return true if $user can view this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user);
}
