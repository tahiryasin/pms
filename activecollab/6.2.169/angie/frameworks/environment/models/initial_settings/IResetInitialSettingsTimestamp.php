<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Reset initial settings time-stamp.
 *
 * @package angie.framework.environment
 * @subpackage models
 */
trait IResetInitialSettingsTimestamp
{
    /**
     * Say hello to the parent object.
     */
    public function IResetInitialSettingsTimestamp()
    {
        $this->registerEventHandler('on_after_save', function ($is_new, $modifications) {
            if ($is_new || !empty($modifications)) {
                AngieApplication::invalidateInitialSettingsCache();
            }
        });
    }

    /**
     * @param  string            $event
     * @param  callable          $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);
}
