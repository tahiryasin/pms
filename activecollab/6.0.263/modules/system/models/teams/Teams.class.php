<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Teams manager class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Teams extends BaseTeams
{
    /**
     * Return new collection.
     *
     * @param  string                    $collection_name
     * @param  User|null                 $user
     * @return ModelCollection
     * @throws ImpossibleCollectionError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'open_assignments_for_team')) {
            $bits = explode('_', $collection_name);
            $team_id = array_pop($bits);

            $team = DataObjectPool::get('Team', $team_id);

            if ($team instanceof Team && $team->countMembers()) {
                $collection = new OpenAssignmentsForTeamCollection($collection_name);
                $collection->setWhosAsking($user)->setTeam($team);
            } else {
                throw new ImpossibleCollectionError("Team #{$team_id} not found, or team is empty");
            }
        } else {
            $collection = parent::prepareCollection($collection_name, $user);

            $collection->setPreExecuteCallback(function ($ids) {
                Users::preloadMemberIdsFromConnectionTable('Team', $ids, 'team_users', 'team_id');
            });
        }

        return $collection;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Return true if $user can create new teams.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isPowerUser();
    }

    // ---------------------------------------------------
    //  Create and update specifics
    // ---------------------------------------------------

    public static function create(array $attributes, bool $save = true, bool $announce = true)
    {
        $team = parent::create($attributes, $save, $announce); // @TODO Announcement should be sent after team members are added

        if ($team instanceof Team && $team->isLoaded()) {
            $team->tryToSetMembersFrom($attributes);
        }

        return $team;
    }

    /**
     * Update an instance.
     *
     * @param  DataObject        $instance
     * @param  array             $attributes
     * @param  bool              $save
     * @return Team
     * @throws InvalidParamError
     */
    public static function &update(DataObject &$instance, array $attributes, $save = true)
    {
        $team = parent::update($instance, $attributes, $save);

        if ($team instanceof Team && $team->isLoaded()) {
            $team->tryToSetMembersFrom($attributes);
        }

        return $team;
    }

    /**
     * Revoke user from all teams where it is a member.
     *
     * @param  User                         $user
     * @param  User                         $by
     * @throws InsufficientPermissionsError
     */
    public static function revokeMember(User $user, User $by)
    {
        if (!$user->canChangeRole($by, false)) {
            throw new InsufficientPermissionsError();
        }

        /** @var Team[] $teams */
        if ($teams = self::findBySQL('SELECT t.* FROM teams AS t LEFT JOIN team_users AS u ON t.id = u.team_id WHERE u.user_id = ?', $user->getId())) {
            foreach ($teams as $team) {
                $team->removeMembers([$user]);
            }
        }
    }
}
