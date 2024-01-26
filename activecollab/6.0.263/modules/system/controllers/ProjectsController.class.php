<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Http\Response\FileDownload\FileDownload;
use Angie\Http\Response\FileDownload\FileDownloadInterface;

AngieApplication::useController('auth_required', SystemModule::NAME);

/**
 * Projects controller.
 *
 * @package ActiveCollab.modules.system
 * @subpackage controllers
 */
class ProjectsController extends AuthRequiredController
{
    /**
     * Active project.
     *
     * @var Project
     */
    protected $active_project;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        if ($project_id = $request->getId('project_id')) {
            if ($this->active_project = DataObjectPool::get('Project', $project_id)) {
                if (!$this->active_project->canView($user)) {
                    return Response::FORBIDDEN;
                }
            } else {
                return Response::NOT_FOUND;
            }
        } else {
            $this->active_project = new Project();
        }
    }

    /**
     * Display main projects page.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function index(Request $request, User $user)
    {
        if ($request->get('sort_by_name')) {
            return Projects::prepareCollection('active_projects_by_name_page_' . $request->getPage(), $user);
        }

        return Projects::prepareCollection('active_projects_page_' . $request->getPage(), $user);
    }

    /**
     * Display filtered projects.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function filter(Request $request, User $user)
    {
        $collection_name = 'filtered_projects';
        // sort part
        $collection_name .= $request->get('sort_by_name') ? '_by_name' : '';
        // filter parts
        $collection_name .= '_client_';
        $collection_name .= $request->get('client') ? $request->get('client') : 'any';
        $collection_name .= '_label_';
        $collection_name .= $request->get('label') ? $request->get('label') : 'any';
        $collection_name .= '_category_';
        $collection_name .= $request->get('category') ? $request->get('category') : 'any';
        // page part
        $collection_name .= '_page_' . $request->getPage();

        return Projects::prepareCollection($collection_name, $user);
    }

    /**
     * List completed projects.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function archive(Request $request, User $user)
    {
        return Projects::prepareCollection('archived_projects_page_' . $request->getPage(), $user);
    }

    /**
     * Return project names.
     *
     * @param  Request              $request
     * @param  User                 $user
     * @return ModelCollection|void
     */
    public function names(Request $request, User $user)
    {
        return Projects::getIdNameMapFor($user);
    }

    /**
     * Return a list of projects with tracking enabled.
     *
     * @param  Request $request
     * @param  User    $user
     * @return array
     */
    public function with_tracking_enabled(Request $request, User $user)
    {
        return Projects::getIdNameMapFor($user, ['projects.is_tracking_enabled = ?', true]);
    }

    /**
     * Return a list of projects with invite people enabled for given user.
     *
     * @param  Request $request
     * @param  User    $user
     * @return array
     */
    public function with_people_permissions(Request $request, User $user)
    {
        return Projects::findWhereUserCanInvitePeople($user);
    }

    /**
     * Show project activity logs.
     *
     * @param  Request $request
     * @param  User    $user
     * @return bool
     */
    public function whats_new(Request $request, User $user)
    {
        AccessLogs::logAccess($this->active_project, $user);

        return Projects::prepareCollection('activity_logs_in_project_' . $this->active_project->getId() . '_page_' . $request->getPage(), $user);
    }

    /**
     * Return project budget.
     *
     * @param  Request $request
     * @param  User    $user
     * @return bool
     */
    public function budget(Request $request, User $user)
    {
        if ($this->active_project->isLoaded() && $this->active_project->canSeeBudget($user)) {
            return Projects::prepareCollection('project_budget_' . $this->active_project->getId(), $user);
        }

        return Response::NOT_FOUND;
    }

    public function additional_data(Request $request, User $user)
    {
        return Projects::prepareCollection(
            'project_additional_data_' . $this->active_project->getId(),
            $user
        );
    }

    /**
     * Which projects to synchronize.
     *
     * @param  Request          $request
     * @param  User             $user
     * @return FileDownload|int
     */
    public function export(Request $request, User $user)
    {
        if ($this->active_project->isLoaded() && $this->active_project->canView($user)) {
            $changes_since = $request->get('changes_since');

            if (ctype_digit($changes_since)) {
                $changes_since = DateTimeValue::makeFromTimestamp((int) $changes_since);
            } elseif ($changes_since) {
                $changes_since = DateTimeValue::makeFromString($changes_since);
            } else {
                $changes_since = null;
            }

            $exported_file = (new ProjectExport($this->active_project, $user, $changes_since))->export();

            if (is_file($exported_file)) {
                return new FileDownload(
                    $exported_file,
                    'application/zip',
                    null,
                    FileDownloadInterface::DOWNLOAD_ATTACHMENT
                );
            }

            return Response::OPERATION_FAILED;
        }

        return Response::NOT_FOUND;
    }

    /**
     * Show project labels (API only).
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function labels(Request $request, User $user)
    {
        return Labels::prepareCollection('project_labels', $user); // @TODO
    }

    /**
     * Show project object as calendar events.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function calendar_events(Request $request, User $user)
    {
        $filter = $request->get('filter');
        $from = $request->get('from');
        $to = $request->get('to');

        return CalendarEvents::prepareCollection('assignments_as_calendar_events_' . $filter . '_' . $from . '_' . $to, $user);
    }

    /**
     * List project categories.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function categories(Request $request, User $user)
    {
        return Categories::prepareCollection('project_categories', $user);
    }

    /**
     * Return project info.
     *
     * @param  Request $request
     * @param  User    $user
     * @return Project
     */
    public function view(Request $request, User $user)
    {
        return $this->active_project;
    }

    /**
     * Create a new project.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function add(Request $request, User $user)
    {
        return Projects::canAdd($user) ? Projects::create($request->post()) : Response::FORBIDDEN;
    }

    /**
     * Update a project.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function edit(Request $request, User $user)
    {
        return $this->active_project->canEdit($user) ? Projects::update($this->active_project, $request->put()) : Response::FORBIDDEN;
    }

    /**
     * Move project to trash.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_project->canDelete($user) ? Projects::scrap($this->active_project) : Response::FORBIDDEN;
    }
}
