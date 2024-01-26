<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Error;

/**
 * Impossible collection error.
 *
 * @package angie.library.database
 * @subpackage errors
 */
class ImpossibleCollectionError extends Error
{
    /**
     * Construct impossible collection error.
     *
     * @param string $message
     */
    public function __construct($message = null)
    {
        if (empty($message)) {
            $message = 'Collection cannot be prepared based on given parameters';
        }

        parent::__construct($message);
    }
}
