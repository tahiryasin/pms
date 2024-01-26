<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Indicator that object can be added to favorites.
 *
 * @package angie.frameworks.favorites
 * @subpackage models
 */
trait IFavoriteImplementation
{
    /**
     * Say hello to the parent object.
     */
    public function IFavoriteImplementation()
    {
        $this->registerEventHandler('on_before_delete', function () {
            Favorites::deleteByParent($this);
        });
    }

    // ---------------------------------------------------
    //  Expectations
    // ---------------------------------------------------

    /**
     * Register an internal event handler.
     *
     * @param $event
     * @param $handler
     * @throws InvalidParamError
     */
    abstract protected function registerEventHandler($event, $handler);
}
