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
interface FirewallInterface
{
    /**
     * Return a white list.
     *
     * @return array
     */
    public function getWhiteList();

    /**
     * Return a black list.
     *
     * @return array
     */
    public function getBlackList();

    /**
     * Check user IP address can pass firewall.
     *
     * @param  IpAddressInterface $ip_address
     * @return mixed
     */
    public function shouldBlock(IpAddressInterface $ip_address);

    /**
     * Check if $ip_address is on white list.
     *
     * @param  IpAddressInterface $ip_address
     * @return mixed
     */
    public function isOnWhiteList(IpAddressInterface $ip_address);

    /**
     * Check if $ip_address is on black list.
     *
     * @param  IpAddressInterface $ip_address
     * @return bool
     */
    public function isOnBlackList(IpAddressInterface $ip_address);
}
