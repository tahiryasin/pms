<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Webhooks integration.
 *
 * @package angie.frameworks.environment
 * @subpackage model
 */
class WebhooksIntegration extends Integration
{
    const JOBS_QUEUE_CHANNEL = 'webhook';

    /**
     * Returns the name of the integration.
     *
     * @return string
     */
    public function getName()
    {
        return 'Webhooks';
    }

    /**
     * Return integration short name.
     *
     * @return string
     */
    public function getShortName()
    {
        return 'webhooks';
    }

    /**
     * Return short integration description.
     *
     * @return string
     */
    public function getDescription()
    {
        return lang("Notify 3rd party services about what's happening in ActiveCollab");
    }

    /**
     * Get group of this integration.
     *
     * @return string
     */
    public function getGroup()
    {
        return 'other';
    }

    /**
     * Returns true if this integration is singleton (can be only one integration of this type in the system).
     *
     * @return bool
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * Check if this integration is in use.
     *
     * @param  User|null $user
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        return (bool) Webhooks::countEnabledForIntegration($this);
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Only owner can access it.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user instanceof Owner;
    }

    // ---------------------------------------------------
    //  Serialization
    // ---------------------------------------------------

    /**
     * Serialize object to json.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $webhooks = Webhooks::prepareCollection('webhooks_integration', null)->execute();

        return array_merge(parent::jsonSerialize(), [
            'webhooks' => !empty($webhooks) ? $webhooks : [],
        ]);
    }
}
