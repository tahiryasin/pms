<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Utils\ProjectTemplateDuplicator\ProjectTemplateDuplicatorInterface;
use Angie\Http\Request;
use Angie\Http\Response;

AngieApplication::useController('auth_required', SystemModule::NAME);

class ProjectTemplatesController extends AuthRequiredController
{
    /**
     * Active project template.
     *
     * @var ProjectTemplate
     */
    protected $active_project_template;

    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if (!Projects::canAdd($user)) {
            return Response::NOT_FOUND;
        }

        $this->active_project_template = DataObjectPool::get(
            ProjectTemplate::class,
            $request->get('project_template_id')
        );

        if (empty($this->active_project_template)) {
            $this->active_project_template = new ProjectTemplate();
        }

        return null;
    }

    /**
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function index(Request $request, User $user)
    {
        return ProjectTemplates::prepareCollection('project_template_active', $user);
    }

    /**
     * @param  Request                    $request
     * @return ProjectTemplate|DataObject
     */
    public function add(Request $request)
    {
        return ProjectTemplates::create($request->post());
    }

    /**
     * @return int|ProjectTemplate
     */
    public function view()
    {
        return $this->active_project_template->isLoaded() ? $this->active_project_template : Response::NOT_FOUND;
    }

    /**
     * @param  Request                        $request
     * @return ProjectTemplate|DataObject|int
     */
    public function edit(Request $request)
    {
        return $this->active_project_template->isLoaded()
            ? ProjectTemplates::update($this->active_project_template, $request->put())
            : Response::NOT_FOUND;
    }

    /**
     * @param  Request   $request
     * @return int|int[]
     */
    public function reorder(Request $request)
    {
        if ($this->active_project_template->isLoaded()) {
            ProjectTemplateElements::reorder($this->active_project_template, $request->put());

            return $request->put();
        }

        return Response::NOT_FOUND;
    }

    /**
     * @param  Request             $request
     * @param  User                $user
     * @return ProjectTemplate|int
     */
    public function duplicate(Request $request, User $user)
    {
        if ($this->active_project_template->isLoaded()) {
            return AngieApplication::getContainer()
                ->get(ProjectTemplateDuplicatorInterface::class)
                    ->duplicate(
                        $this->active_project_template,
                        $user,
                        $request->post('name')
                    );
        }

        return Response::NOT_FOUND;
    }

    /**
     * @return int
     */
    public function delete()
    {
        return $this->active_project_template->isLoaded()
            ? ProjectTemplates::scrap($this->active_project_template)
            : Response::NOT_FOUND;
    }
}
