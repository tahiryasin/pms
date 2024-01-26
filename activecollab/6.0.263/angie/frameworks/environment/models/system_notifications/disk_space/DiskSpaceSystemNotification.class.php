<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Disk space system notification.
 *
 * @package angie.environment
 * @subpackage models
 */
class DiskSpaceSystemNotification extends SystemNotification
{
    /**
     * Return notification title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return lang('Disk space limit exceeded!');
    }

    /**
     * Return notification body.
     *
     * @return mixed
     */
    public function getBody()
    {
        return lang("You've used up more storage than your subscription allows (:space). Please switch to a larger plan to be able to upload new files.", [
            'space' => defined('ON_DEMAND_PLAN_MAX_DISK_SPACE') ? format_file_size(ON_DEMAND_PLAN_MAX_DISK_SPACE) : format_file_size(0),
        ]);
    }

    /**
     * Return notification action.
     *
     * @return mixed
     */
    public function getAction()
    {
        return lang('Upgrade Plan');
    }

    /**
     * Return notification url.
     *
     * @return mixed
     */
    public function getUrl()
    {
        return ROOT_URL . '/subscription/choose-plan';
    }

    /**
     * Return is permanent.
     *
     * @return mixed
     */
    public function isPermanent()
    {
        return true;
    }
}
