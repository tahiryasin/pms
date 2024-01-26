<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Application level attachments class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class Attachments extends FwAttachments
{
    /**
     * Return new collection.
     *
     * @param  string            $collection_name
     * @param  User|null         $user
     * @return ModelCollection
     * @throws InvalidParamError
     */
    public static function prepareCollection($collection_name, $user)
    {
        if (str_starts_with($collection_name, 'attachments_in_project')) {
            $bits = explode('_', $collection_name);
            $project_id = array_pop($bits);
        } else {
            $project_id = null;
        }

        $project = DataObjectPool::get('Project', $project_id);

        if ($project instanceof Project) {
            $collection = parent::prepareCollection($collection_name, $user);

            $project->getTypeIdsMapOfPotentialAttachmentParents();

            return $collection;
        } else {
            throw new InvalidParamError('collection_name', $collection_name, 'Invalid collection name');
        }
    }
}
