<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('auth_required', EnvironmentFramework::INJECT_INTO);

/**
 * Job types controller.
 *
 * @package ActiveCollab.modules tracking
 * @subpackage controllers
 */
final class JobTypesController extends AuthRequiredController
{
    /**
     * @var JobType
     */
    protected $active_job_type;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        $this->active_job_type = DataObjectPool::get('JobType', $request->getId('job_type_id'));
        if (empty($this->active_job_type)) {
            $this->active_job_type = new JobType();
        }
    }

    /**
     * Return all job types.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function index(Request $request, User $user)
    {
        return JobTypes::prepareCollection(DataManager::ALL, $user);
    }

    /**
     * Create a new job type
     * If job type with that name exists, remove it from archive.
     *
     * @param  Request     $request
     * @param  User        $user
     * @return JobType|int
     */
    public function add(Request $request, User $user)
    {
        return JobTypes::canAdd($user) ? JobTypes::create($request->post()) : Response::NOT_FOUND;
    }

    /**
     * Return a single job type.
     *
     * @param  Request     $request
     * @param  User        $user
     * @return JobType|int
     */
    public function view(Request $request, User $user)
    {
        return $this->active_job_type->isLoaded() && $this->active_job_type->canView($user) ? $this->active_job_type : Response::NOT_FOUND;
    }

    /**
     * Update a job type.
     *
     * @param  Request     $request
     * @param  User        $user
     * @return JobType|int
     */
    public function edit(Request $request, User $user)
    {
        return $this->active_job_type->isLoaded() && $this->active_job_type->canEdit($user) ? JobTypes::update($this->active_job_type, $request->put()) : Response::NOT_FOUND;
    }

    /**
     * Batch edit job types.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return array|JobType[]
     */
    public function batch_edit(Request $request, User $user)
    {
        return $user->isOwner() ? JobTypes::batchEdit($request->put()) : [];
    }

    /**
     * Delete job type
     * If job type is used, move it to archive.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return bool|int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_job_type->isLoaded() && $this->active_job_type->canDelete($user) ? JobTypes::scrap($this->active_job_type) : Response::NOT_FOUND;
    }

    /**
     * @return JobType|int
     */
    public function view_default()
    {
        if ($job_type = DataObjectPool::get('JobType', JobTypes::getDefaultId())) {
            return $job_type;
        }

        return Response::NOT_FOUND;
    }

    /**
     * @param  Request     $request
     * @param  User        $user
     * @return JobType|int
     */
    public function set_default(Request $request, User $user)
    {
        if ($user->isOwner()) {
            /** @var JobType $job_type */
            if ($job_type = DataObjectPool::get('JobType', $request->post('job_type_id'))) {
                return JobTypes::setDefault($job_type);
            }

            return Response::BAD_REQUEST;
        }

        return Response::NOT_FOUND;
    }
}
