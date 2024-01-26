<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Module\System\Utils\Dependency\ProjectTemplateDependencyResolverInterface;
use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\StatusResponse\StatusResponse;
use Angie\Http\Response\StatusResponse\StatusResponseInterface;

AngieApplication::useController('project_templates', SystemModule::NAME);

/**
 * Project template elements controller.
 *
 * @package ActiveCollab.modules.system
 * @subpackage controllers
 */
class ProjectTemplateElementsController extends ProjectTemplatesController
{
    /**
     * @var ProjectTemplateElement
     */
    private $active_project_template_element;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if (!$this->active_project_template->isLoaded()) {
            return Response::NOT_FOUND;
        }

        $this->active_project_template_element = DataObjectPool::get('ProjectTemplateElement', $request->get('project_template_element_id'));

        if ($this->active_project_template_element instanceof ProjectTemplateElement && $this->active_project_template_element->getTemplateId() != $this->active_project_template->getId()) {
            return Response::NOT_FOUND;
        }
    }

    /**
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function index(Request $request, User $user)
    {
        return ProjectTemplateElements::prepareCollection('elements_in_template_' . $this->active_project_template->getId(), $user);
    }

    /**
     * @return int|ProjectTemplateElement
     */
    public function view()
    {
        return $this->active_project_template_element instanceof ProjectTemplateElement ? $this->active_project_template_element : Response::NOT_FOUND;
    }

    /**
     * Provide download for template file.
     *
     * @return \Angie\Http\Response\FileDownload\FileDownload|int
     */
    public function download()
    {
        return $this->active_project_template_element instanceof ProjectTemplateFile ? $this->active_project_template_element->prepareForDownload() : Response::NOT_FOUND;
    }

    /**
     * @param  Request                                        $request
     * @return StatusResponseInterface|ProjectTemplateElement
     */
    public function add(Request $request)
    {
        $post = $request->post();
        $post['template_id'] = $this->active_project_template->getId();
        $result = ProjectTemplateElements::create($post);
        if ($result instanceof ValidationErrors) {
            return new StatusResponse(
                Response::BAD_REQUEST,
                '',
                ['message' => lang($result->getMessage())]
            );
        }

        return $result;
    }

    /**
     * Batch add elements.
     *
     * @param  Request           $request
     * @return array
     * @throws InvalidParamError
     */
    public function batch_add(Request $request)
    {
        $result = [];

        $post = $request->post();
        if ($post && is_array($post)) {
            foreach ($post as &$p) {
                $p['template_id'] = $this->active_project_template->getId();
            }

            $result = ProjectTemplateElements::createMany($post);
        }

        return $result;
    }

    /**
     * @param  Request                    $request
     * @return int|ProjectTemplateElement
     */
    public function edit(Request $request)
    {
        return $this->active_project_template_element instanceof ProjectTemplateElement ? ProjectTemplateElements::update($this->active_project_template_element, $request->put()) : Response::NOT_FOUND;
    }

    /**
     * @return bool|int
     */
    public function delete()
    {
        if ($this->active_project_template_element instanceof IProjectTemplateTaskDependency) {
            AngieApplication::getContainer()
                ->get(ProjectTemplateDependencyResolverInterface::class)
                ->deleteDependencies($this->active_project_template_element);
        }
        if ($this->active_project_template_element instanceof ProjectTemplateElement) {
            return ProjectTemplateElements::scrap($this->active_project_template_element);
        }

        return Response::NOT_FOUND;
    }
}
