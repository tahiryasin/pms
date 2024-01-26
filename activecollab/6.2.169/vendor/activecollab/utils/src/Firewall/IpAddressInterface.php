<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Firewall;

/**
 * @package ActiveCollab\Firewall
 */
interface IpAddressInterface
{
    /**
     * Check if address is on the given list of rules.
     *
     * @param  array $list
     * @return bool
     */
    public function isOnList(array $list);
}
