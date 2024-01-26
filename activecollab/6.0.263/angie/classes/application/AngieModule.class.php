<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Angie module definition.
 *
 * @package angie.library.application
 */
abstract class AngieModule extends AngieFramework
{
    /**
     * Return full framework path.
     *
     * @return string
     */
    public function getPath()
    {
        return APPLICATION_PATH . '/modules/' . $this->name;
    }
}
