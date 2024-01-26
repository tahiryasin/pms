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
 * Selected object controller implementation.
 *
 * @package angie.frameworks.environment
 * @subpackage controllers
 */
abstract class FwSelectedObjectController extends AuthRequiredController
{
    /**
     * Selected object.
     *
     * @var DataObject
     */
    protected $active_object;

    /**
     * Instance of check after object gets loaded.
     *
     * @var string
     */
    protected $active_object_instance_of = 'DataObject';

    /**
     * {@inheritdoc}
     */
    protected function __before(Request $request, $user)
    {
        $before_result = parent::__before($request, $user);

        if ($before_result !== null) {
            return $before_result;
        }

        $parent_type = $request->get('parent_type');

        if ($parent_type) {
            $parent_type = Angie\Inflector::camelize(str_replace('-', '_', $parent_type));
        }

        $parent_id = $request->getId('parent_id');

        if (class_exists($parent_type) && is_subclass_of($parent_type, 'DataObject')) {
            $this->active_object = DataObjectPool::get($parent_type, $parent_id);
        }

        if (!($this->active_object instanceof $this->active_object_instance_of && method_exists($this->active_object, 'canView') && $this->active_object->canView($user))) {
            return Response::NOT_FOUND;
        }
    }
}
