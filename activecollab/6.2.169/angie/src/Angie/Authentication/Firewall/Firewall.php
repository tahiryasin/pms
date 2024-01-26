<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Authentication\Firewall;

use ActiveCollab\Firewall\Firewall as BaseFirewall;
use ActiveCollab\Firewall\IpAddressInterface;

/**
 * @package Angie\Authentication\Firewall
 */
class Firewall extends BaseFirewall
{
    /**
     * @var bool
     */
    private $is_enabled;

    /**
     * Firewall constructor.
     *
     * @param bool  $is_enabled
     * @param array $white_list
     * @param array $black_list
     * @param bool  $validate_rules
     */
    public function __construct($is_enabled, array $white_list, array $black_list, $validate_rules = true)
    {
        parent::__construct($white_list, $black_list, $validate_rules);

        $this->is_enabled = $is_enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBlock(IpAddressInterface $ip_address)
    {
        if ($this->is_enabled) {
            return parent::shouldBlock($ip_address);
        }

        return false;
    }
}
