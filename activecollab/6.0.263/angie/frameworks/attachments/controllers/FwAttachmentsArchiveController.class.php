<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Angie\Http\Response;
use Angie\Inflector;

AngieApplication::useController('auth_required', EnvironmentFramework::INJECT_INTO);

/**
 * Framework level attachments controller.
 *
 * @package angie.frameworks.attachments
 * @subpackage controllers
 */
abstract class FwAttachmentsArchiveController extends AuthRequiredController
{
    /**
     * @var IAttachments
     */
    protected $active_parent;

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
            $parent_type = Inflector::camelize(str_replace('-', '_', $parent_type));
        }

        $this->active_parent = DataObjectPool::get($parent_type, $request->getId('parent_id'));

        if (!($this->active_parent instanceof IAttachments && $this->active_parent->canView($user))) {
            return Response::NOT_FOUND;
        }
    }

    /**
     * Prepare attachments archive.
     *
     * @return array
     */
    public function prepare()
    {
        /** @var WarehouseIntegration $warehouse_integration */
        $warehouse_integration = Integrations::findFirstByType(WarehouseIntegration::class);

        if ($warehouse_integration->isInUse()) {
            return $warehouse_integration->prepareForFilesArchive($this->active_parent);
        }

        return (new AttachmentsArchive($this->active_parent))->prepare();
    }
}
