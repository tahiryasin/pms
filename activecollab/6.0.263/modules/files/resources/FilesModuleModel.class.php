<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

require_once APPLICATION_PATH . '/resources/ActiveCollabModuleModel.class.php';

/**
 * Files module model definition.
 *
 * @package activeCollab.modules.files
 * @subpackage resources
 */
class FilesModuleModel extends ActiveCollabModuleModel
{
    /**
     * Construct attachments framework model definition.
     *
     * @param FilesModule $parent
     */
    public function __construct(FilesModule $parent)
    {
        parent::__construct($parent);

        $this->addModelFromFile('files')
            ->setTypeFromField('type')
            ->implementHistory()
            ->implementAccessLog()
            ->implementSearch()
            ->implementTrash()
            ->implementActivityLog()
            ->addModelTrait('IHiddenFromClients')
            ->addModelTrait('IProjectElement', 'IProjectElementImplementation')
            ->addModelTraitTweak('IProjectElementImplementation::canViewAccessLogs insteadof IAccessLogImplementation')
            ->addModelTraitTweak('IProjectElementImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation');
    }

    /**
     * Load initial module data.
     */
    public function loadInitialData()
    {
        $this->addConfigOption('display_mode_project_files', 'grid');

        parent::loadInitialData();
    }
}
