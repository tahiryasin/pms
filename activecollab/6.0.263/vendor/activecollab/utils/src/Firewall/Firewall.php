<?php

/*
 * This file is part of the Active Collab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace ActiveCollab\Firewall;

use InvalidArgumentException;

/**
 * @package ActiveCollab\Firewall
 */
class Firewall implements FirewallInterface
{
    /**
     * @var array
     */
    private $white_list;

    /**
     * @var array
     */
    private $black_list;

    /**
     * Firewall constructor.
     *
     * @param array $white_list
     * @param array $black_list
     * @param bool  $validate_rules
     */
    public function __construct(array $white_list, array $black_list, $validate_rules = true)
    {
        if ($validate_rules) {
            if (!$this->validateList($white_list)) {
                throw new InvalidArgumentException('Rules on firewall white list are not valid.');
            }

            if (!$this->validateList($black_list)) {
                throw new InvalidArgumentException('Rules on firewall black list are not valid.');
            }
        }

        $this->white_list = $white_list;
        $this->black_list = $black_list;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhiteList()
    {
        return $this->white_list;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlackList()
    {
        return $this->black_list;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBlock(IpAddressInterface $ip_address)
    {
        if ($this->isOnWhiteList($ip_address)) {
            return false;
        }

        if ($this->isOnBlackList($ip_address)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isOnWhiteList(IpAddressInterface $ip_address)
    {
        return $ip_address->isOnList($this->getWhiteList());
    }

    /**
     * {@inheritdoc}
     */
    public function isOnBlackList(IpAddressInterface $ip_address)
    {
        return $ip_address->isOnList($this->getBlackList());
    }

    /**
     * Validate list of firewall rules.
     *
     * @param  array $list
     * @return bool
     */
    private function validateList(array $list)
    {
        foreach ($list as $rule) {
            if (!$this->validateRule($rule)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate filtering rule.
     *
     * @param  string $rule
     * @return bool
     */
    private function validateRule($rule)
    {
        if (strpos($rule, '/')) {
            list($rule, $mask) = explode('/', $rule);
        }

        if (filter_var($rule, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (isset($mask) && ($mask < 1 || $mask > 30)) {
                return false;
            }

            return true;
        } elseif (filter_var($rule, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if (isset($mask) && ($mask < 1 || $mask > 128)) {
                return false;
            }

            return true;
        }

        return false;
    }
}
