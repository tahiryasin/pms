<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

declare(strict_types=1);

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

/**
 * Integration class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
interface IntegrationInterface extends RoutingContextInterface
{
    /**
     * Return integration object ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Return short integration description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get group of this integration.
     *
     * @return string
     */
    public function getGroup();

    /**
     * Get group order of this integration.
     *
     * @return int|null
     */
    public function getGroupOrder();

    /**
     * Returns true if this integration is in use.
     *
     * For non-singleton integration, this method will do a simple counter check or complex properties check (using a
     * callback return by getIsInUseAdditionalPropertiesChecker() method) to determine whether integration is in use or
     * not.
     *
     * @param  User $user
     * @return bool
     */
    public function isInUse(User $user = null);

    /**
     * Return webhooks created and owned by this integration.
     *
     * @return Webhook[]|null
     */
    public function getWebhooks();

    /**
     * Returns true if this integration is provided by a third party.
     *
     * @return bool
     */
    public function isThirdParty();

    /**
     * Return true if this integration is available for self-hosted packages.
     *
     * @return bool
     */
    public function isAvailableForSelfHosted();

    /**
     * Return true if this integration is available for on-demand packages.
     *
     * @return bool
     */
    public function isAvailableForOnDemand();

    /**
     * Returns true if this integration is singleton (can be only one integration of this type in the system).
     *
     * @return bool
     */
    public function isSingleton();

    /**
     * Return integration short name.
     */
    public function getShortName();
}
