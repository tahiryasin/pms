<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Project category class.
 *
 * @package activeCollab.modules.system
 * @subpackage models
 */
class ProjectCategory extends Category
{
    /**
     * Check if $user can update this project category.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isPowerUser();
    }

    /**
     * Return true if $user can delete this category.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isPowerUser();
    }

    // ---------------------------------------------------
    //  System
    // ---------------------------------------------------

    /**
     * Delete this object from database.
     *
     * @param  bool      $bulk
     * @throws Exception
     */
    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Removing project category @ ' . __CLASS__);

            /** @var Project[] $projects */
            if ($projects = Projects::find(['conditions' => ['category_id = ?', $this->getId()]])) {
                foreach ($projects as $project) {
                    $project->setCategoryId(0);
                    $project->save();
                }
            }

            parent::delete($bulk);

            DB::commit('Project category removed @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to remove project category @ ' . __CLASS__);

            throw $e;
        }
    }
}
