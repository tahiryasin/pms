<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Memories;

use ActiveCollab\Memories\Memories;
use InvalidArgumentException;

interface MemoriesWrapperInterface
{
    /**
     * Return Memories instance.
     *
     * @return Memories
     */
    public function getInstance(): Memories;

    /**
     * Return value that is stored under the key. If value is not found, $if_not_found_return should be returned.
     *
     * Set $use_cache to false if you want this method to ignore cached values
     *
     * @param  string     $key
     * @param  mixed|null $if_not_found_return
     * @param  bool       $use_cache
     * @return mixed
     */
    public function get($key, $if_not_found_return = null, $use_cache = true);

    /**
     * Set a value for the given key.
     *
     * @param  string                   $key
     * @param  mixed                    $value
     * @param  bool                     $bulk
     * @return array
     * @throws InvalidArgumentException
     */
    public function set($key, $value = null, $bulk = false);

    /**
     * Forget a value that we have stored under the $key.
     *
     * @param string     $key
     * @param bool|false $bulk
     */
    public function forget($key, $bulk = false): void;
}
