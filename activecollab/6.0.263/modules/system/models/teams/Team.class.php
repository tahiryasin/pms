<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Team class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Team extends BaseTeam
{
    public function getRoutingContext(): string
    {
        return 'team';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'team_id' => $this->getId(),
        ];
    }

    /**
     * Return true if $user can view this team.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return true;
    }

    /**
     * Return true if $user can delete this team.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $this->canEdit($user);
    }

    /**
     * Return true if $user can update this team.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isPowerUser() || $this->isCreatedBy($user);
    }

    /**
     * Validate before save.
     *
     * @param ValidationErrors $errors
     */
    public function validate(ValidationErrors &$errors)
    {
        if ($this->validatePresenceOf('name')) {
            $this->validateUniquenessOf('name') or $errors->addError('Team name needs to be unique', 'name');
        } else {
            $errors->fieldValueIsRequired('name');
        }
    }
}
