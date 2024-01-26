<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('auth_required', EnvironmentFramework::INJECT_INTO);

class UsersController extends AuthRequiredController
{
    /**
     * Selected user account.
     *
     * @var User
     */
    protected $active_user;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        $this->active_user = Users::findById($request->getId('user_id'));

        if (empty($this->active_user)) {
            $this->active_user = Users::getUserInstance();
        }
    }

    /**
     * Show users index page.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function index(Request $request, User $user)
    {
        return Users::prepareCollection(Users::ACTIVE, $user);
    }

    /**
     * Show all users.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function all(Request $request, User $user)
    {
        return Users::prepareCollection(Users::ALL, $user);
    }

    /**
     * Show archived users.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function archive(Request $request, User $user)
    {
        return Users::prepareCollection(Users::ARCHIVED, $user);
    }

    /**
     * Check email address.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return int|array
     */
    public function check_email(Request $request, User $user)
    {
        if (!Users::canAdd($user)) {
            return Response::NOT_FOUND;
        }

        $email = $request->get('email');

        $result = ['valid_address' => false, 'in_use' => false, 'user_id' => null];

        if ($email && is_valid_email($email)) {
            $result['valid_address'] = true;

            $user = Users::findByEmail($email, true);

            if ($user instanceof User) {
                $result['user_id'] = $user->getId();

                if ($user->getIsTrashed()) {
                    $result['in_use'] = 'trashed';
                } elseif ($user->getIsArchived()) {
                    $result['in_use'] = 'archived';
                } elseif ($user->isPendingActivation()) {
                    $result['in_use'] = 'invited';
                } else {
                    $result['in_use'] = 'active';
                }
            }
        }

        return $result;
    }

    /**
     * Invite multiple users.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function invite(Request $request, User $user)
    {
        $invitation_request = $request->post();

        $email_addresses = array_required_var($invitation_request, 'email_addresses', true);
        $role = array_required_var($invitation_request, 'role', true);
        $custom_permissions = array_var($invitation_request, 'custom_permissions', [], true);

        if (Users::canAddAs($user, $role, $custom_permissions)) {
            return Users::invite($user, $email_addresses, $role, $custom_permissions, $invitation_request);
        }

        return Response::FORBIDDEN;
    }

    /**
     * Create a new user account.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return User|int
     */
    public function add(Request $request, User $user)
    {
        $post = $request->post();

        $role = array_required_var($post, 'type');
        $custom_permissions = array_var($post, 'custom_permissions', []);

        if (Users::canAddAs($user, $role, $custom_permissions)) {
            return Users::create($post);
        }

        return Response::FORBIDDEN;
    }

    /**
     * View an account of a single user.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return int|User
     */
    public function view(Request $request, User $user)
    {
        return $this->active_user->isLoaded() && $this->active_user->canView($user) ? $this->active_user : Response::NOT_FOUND;
    }

    /**
     * Update existing user account.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return int|User
     */
    public function edit(Request $request, User $user)
    {
        $put = $request->put();

        if (isset($put['password']) || array_key_exists('password', $put)) {
            return Response::BAD_REQUEST;
        }

        return $this->active_user->isLoaded() && $this->active_user->canEdit($user) ? Users::update($this->active_user, $request->put()) : Response::FORBIDDEN;
    }

    /**
     * Change user account.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return int|User
     */
    public function change_user_profile(Request $request, User $user)
    {
        $allowed_fields = ['first_name', 'last_name', 'email', 'language_id'];

        $put = $request->put();

        foreach ($put as $k => $value) {
            if (!in_array($k, $allowed_fields)) {
                return Response::BAD_REQUEST;
            }
        }

        return $this->active_user->changeProfile($user, $put);
    }

    /**
     * Edit user password.
     *
     * @param  Request          $request
     * @param  User             $user
     * @return User|int
     * @throws ValidationErrors
     */
    public function change_password(Request $request, User $user)
    {
        if (AngieApplication::authentication()->getLoginPolicy()->isPasswordChangeEnabled() && $this->active_user->isLoaded() && $this->active_user->canEdit($user)) {
            $change_password_data = $request->put();

            if (is_array($change_password_data) && isset($change_password_data['my_password']) && isset($change_password_data['new_password']) && isset($change_password_data['new_password_again'])) {
                return $this->active_user->changePassword($user, $change_password_data['my_password'], $change_password_data['new_password'], $change_password_data['new_password_again']);
            }

            return Response::BAD_REQUEST;
        }

        return Response::NOT_FOUND;
    }

    /**
     * Change user password.
     *
     * @param  Request          $request
     * @param  User             $user
     * @return User|int
     * @throws ValidationErrors
     */
    public function change_user_password(Request $request, User $user)
    {
        $data = $request->put();

        $errors = new ValidationErrors();
        if (!isset($data['new_password']) || !isset($data['new_password_again'])) {
            $errors->addError('Fields are required');
        }
        if ($data['new_password'] !== $data['new_password_again']) {
            $errors->addError('Password do not match', 'new_password');
        }
        if ($errors->hasErrors()) {
            throw $errors;
        }

        if ($this->active_user->canChangeUserPassowrd($user)) {
            $old_password = isset($data['old_password']) ? $data['old_password'] : null;

            return $this->active_user->forceChangePassword(
                $user,
                $data['new_password'],
                $data['new_password_again'],
                $old_password
            );
        }

        return Response::FORBIDDEN;
    }

    /**
     * Change user role.
     *
     * @param  Request      $request
     * @param  User         $user
     * @return User|int
     * @throws Exception
     * @throws \Angie\Error
     */
    public function change_role(Request $request, User $user)
    {
        if ($this->active_user->isLoaded()) {
            $new_role = $request->post('role');
            $custom_permissions = $request->post('custom_permissions');

            if (AngieApplication::isOnDemand() && !$this->active_user->canManageTasks() && !OnDemand::canAddUsersBasedOnCurrentPlan($new_role, $custom_permissions, 1, [$user->getEmail()])) {
                throw new \Angie\Error("Can't change user role, check your plan restriction.");
            }

            if ($this->active_user->canChangeRole($user, $custom_permissions)) {
                return Users::changeUserType($this->active_user, $new_role, $custom_permissions, $user);
            }

            return Response::FORBIDDEN;
        }

        return Response::NOT_FOUND;
    }

    /**
     * Resend invitation.
     *
     * @param  Request            $request
     * @param  User               $user
     * @return UserInvitation|int
     */
    public function resend_invitation(Request $request, User $user)
    {
        if ($this->canManageUsersInvitation($user, $this->active_user)) {
            return $this->active_user->invite($user, null, true);
        }

        return Response::NOT_FOUND;
    }

    /**
     * Get invitation for the user.
     *
     * @param  Request            $request
     * @param  User               $user
     * @return UserInvitation|int
     */
    public function get_invitation(Request $request, User $user)
    {
        if ($this->canManageUsersInvitation($user, $this->active_user)) {
            return $this->active_user->getInvitation();
        }

        return Response::NOT_FOUND;
    }

    /**
     * Get invitation URL for the user.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return array|int
     */
    public function get_accept_invitation_url(Request $request, User $user)
    {
        if ($this->canManageUsersInvitation($user, $this->active_user)) {
            $invitation = $this->active_user->getInvitation();

            if ($invitation instanceof UserInvitation) {
                return [
                    'is_ok' => true,
                    'accept_invitation_url' => $invitation->getAcceptUrl(),
                ];
            }
        }

        return Response::NOT_FOUND;
    }

    /**
     * Return TRUE if $user can manage $for_user's invitation.
     *
     * @param  User $user
     * @param  User $for_user
     * @return bool
     */
    private function canManageUsersInvitation($user, $for_user)
    {
        return Users::canAdd($user) && $for_user->isLoaded() && $for_user->canBeInvited();
    }

    /**
     * List activties performed by selected user.
     *
     * @param  Request             $request
     * @param  User                $user
     * @return int|ModelCollection
     */
    public function activities(Request $request, User $user)
    {
        return $this->active_user->isLoaded() && $this->active_user->canView($user) ? Users::prepareCollection('activity_logs_by_' . $this->active_user->getId() . '_page_' . $request->getPage(), $user) : Response::NOT_FOUND;
    }

    /**
     * Clear user avatar.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function clear_avatar(Request $request, User $user)
    {
        if ($this->active_user->isLoaded() && $this->active_user->canEdit($user)) {
            $this->active_user->clearAvatar();

            return Response::OK;
        }

        return Response::NOT_FOUND;
    }

    /**
     * Move user to trash.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_user->isLoaded() && $this->active_user->canDelete($user) ? Users::scrap($this->active_user) : Response::NOT_FOUND;
    }

    /**
     * Show active projects that selected user is involved with.
     *
     * @param  Request             $request
     * @param  User                $user
     * @return ModelCollection|int
     */
    public function projects(Request $request, User $user)
    {
        return $this->active_user->isLoaded() && $this->active_user->canView($user) ? Projects::prepareCollection('active_user_projects_' . $this->active_user->getId() . '_page_' . $request->getPage(), $user) : Response::NOT_FOUND;
    }

    /**
     * Show ids of active projects that selected user is involved with.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return array|int
     */
    public function project_ids(Request $request, User $user)
    {
        if ($this->active_user->isLoaded() && $this->active_user->canView($user)) {
            if ($this->active_user->getProjectIds()) {
                return $this->active_user->getProjectIds();
            }

            return [];
        }

        return Response::NOT_FOUND;
    }

    /**
     * Add user to many projects.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function add_to_projects(Request $request, User $user)
    {
        return $this->active_user->isLoaded() && $this->active_user->canView($user) ? Projects::addUserToManyProjects($user, $this->active_user, $request->post()) : Response::NOT_FOUND;
    }

    /**
     * Return permissions for changing user profile.
     *
     * @param  Request $request
     * @param  User    $user
     * @return array
     */
    public function profile_permissions(Request $request, User $user)
    {
        $can_change_profile = $this->active_user->canChangeUserProfile($user);

        return [
            'can_change_profile' => $can_change_profile,
            'can_change_name' => !$can_change_profile ? $this->active_user->canChangeUserName($user) : true,
        ];
    }

    /**
     * Return permissions for changing user password.
     *
     * @param  Request $request
     * @param  User    $user
     * @return array
     */
    public function password_permissions(Request $request, User $user)
    {
        return [
            'can_change_password' => $this->active_user->canChangeUserPassowrd($user),
            'is_same_user' => $user->getId() === $this->active_user->getId(),
        ];
    }
}
