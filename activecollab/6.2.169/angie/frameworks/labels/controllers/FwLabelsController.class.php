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
 * Framework level  labels controller.
 *
 * @package angie.frameworks.labels
 * @subpackage controllers
 */
abstract class FwLabelsController extends AuthRequiredController
{
    /**
     * Parent object instance.
     *
     * @var ILabels|ApplicationObject
     */
    protected $active_label;

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        $this->active_label = DataObjectPool::get('Label', $request->getId('label_id'));
    }

    /**
     * List object labels.
     *
     * @param  Request         $request
     * @param  User            $user
     * @return ModelCollection
     */
    public function index(Request $request, User $user)
    {
        return Labels::prepareCollection(DataManager::ALL, $user);
    }

    /**
     * Reorder labels.
     *
     * @param  Request $request
     * @param  User    $user
     * @return int
     */
    public function reorder(Request $request, User $user)
    {
        if (Labels::canReorder($user)) {
            Labels::reorder($request->put());

            return $request->put();
        }

        return Response::NOT_FOUND;
    }

    /**
     * View a signle label.
     *
     * @param  Request   $request
     * @param  User      $user
     * @return Label|int
     */
    public function view(Request $request, User $user)
    {
        return $this->active_label instanceof Label && $this->active_label->isLoaded() && $this->active_label->canView($user) ? $this->active_label : Response::NOT_FOUND;
    }

    /**
     * Define a new label.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function add(Request $request, User $user)
    {
        return Labels::canAdd($user) ? Labels::create($request->post()) : Response::NOT_FOUND;
    }

    /**
     * Update the selected label.
     *
     * @param  Request        $request
     * @param  User           $user
     * @return DataObject|int
     */
    public function edit(Request $request, User $user)
    {
        return $this->active_label instanceof Label && $this->active_label->isLoaded() && $this->active_label->canEdit($user) ? Labels::update($this->active_label, $request->put()) : Response::NOT_FOUND;
    }

    /**
     * Delete the selected label.
     *
     * @param  Request  $request
     * @param  User     $user
     * @return bool|int
     */
    public function delete(Request $request, User $user)
    {
        return $this->active_label instanceof Label && $this->active_label->isLoaded() && $this->active_label->canDelete($user) ? Labels::scrap($this->active_label) : Response::NOT_FOUND;
    }

    /**
     * Set label as default.
     *
     * @param Request $request
     * @param User    $user
     */
    public function set_as_default(Request $request, User $user)
    {
        if ($this->active_label instanceof Label && $this->active_label->isLoaded() && $this->active_label->canEdit($user)) {
            if ($this->active_label->getIsDefault()) {
                Labels::unsetDefault($this->active_label);
            } else {
                Labels::setDefault($this->active_label);
            }

            return $this->active_label;
        }

        return Response::NOT_FOUND;
    }
}
