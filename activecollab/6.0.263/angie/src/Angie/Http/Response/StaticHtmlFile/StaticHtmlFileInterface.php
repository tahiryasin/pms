<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Http\Response\StaticHtmlFile;

/**
 * @package Angie\Http\Response\StaticHtmlFile
 */
interface StaticHtmlFileInterface
{
    /**
     * Return file path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Return options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * @return string
     */
    public function getContent();
}
