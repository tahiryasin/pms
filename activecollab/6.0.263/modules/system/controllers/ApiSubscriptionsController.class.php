<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('users', EnvironmentFramework::INJECT_INTO);

/**
 * Application level API subscriptions controller.
 *
 * @package activeCollab.modules.system
 * @subpackage controllers
 */
class ApiSubscriptionsController extends UsersController
{
    /**
     * Selected API client subscription.
     *
     * @var ApiSubscription
     */
    protected $active_api_subscription;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if ($this->active_user->isNew() || !$this->active_user->canManageApiSubscriptions($user)) {
            return Response::NOT_FOUND;
        }

        $this->active_api_subscription = DataObjectPool::get(ApiSubscription::class, $request->getId('api_subscription_id'));

        if (empty($this->active_api_subscription)) {
            $this->active_api_subscription = new ApiSubscription();
        }
    }

    /**
     * List API subscriptions for the given user.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function index(Request $request, User $user)
    {
        return ApiSubscriptions::prepareCollection('api_subscriptions_for_' . $this->active_user->getId(), $user);
    }

    /**
     * Create a new subscription.
     *
     * @param  Request             $request
     * @return ApiSubscription|int
     */
    public function add(Request $request)
    {
        $post = $request->post();

        if (!is_array($post)) {
            $post = [];
        }

        $post['user_id'] = $this->active_user->getId();
        $post['is_enabled'] = true;

        if (isset($post['token'])) {
            unset($post['token']);
        }

        $last_login_on = $this->active_user->getLastLoginOn();

        $api_subscription = ApiSubscriptions::create($post);

        if ($last_login_on instanceof DateTimeValue && $this->active_user->getLastLoginOn() instanceof DateTimeValue) {
            $this->active_user->triggerEventActivateUser();
        }

        return $api_subscription;
    }

    /**
     * View subscription details.
     *
     * @return ApiSubscription|int
     */
    public function view()
    {
        return $this->active_api_subscription->isLoaded() ? $this->active_api_subscription : Response::NOT_FOUND;
    }

    /**
     * Update a single subscription.
     *
     * @return ApiSubscription|int
     */
    public function edit()
    {
        return Response::NOT_FOUND;
    }

    /**
     * Delete a subscription.
     *
     * @return bool|int
     */
    public function delete()
    {
        return $this->active_api_subscription->isLoaded() ? ApiSubscriptions::scrap($this->active_api_subscription) : Response::NOT_FOUND;
    }
}
