<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
class NewRelicIntegration extends Integration
{
    /**
     * {@inheritdoc}
     */
    public function isSingleton()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'New Relic';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortName()
    {
        return 'new-relic';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return lang('Allow ActiveCollab monitoring');
    }

    /**
     * {@inheritdoc}
     */
    public function isInUse(User $user = null)
    {
        return $this->getLicense() && $this->getAppKey();
    }

    /**
     * Get new relic licnese.
     *
     * @return string
     */
    private function getLicense()
    {
        return defined('NEW_RELIC_LICENSE')
            ? NEW_RELIC_LICENSE
            : $this->getAdditionalProperty('license');
    }

    /**
     * Set new relic license.
     *
     * @param  string $value
     * @return string
     */
    public function setLicense($value)
    {
        if (!AngieApplication::isOnDemand()) {
            $this->setAdditionalProperty('license', $value);
        }

        return $this->getLicense();
    }

    /**
     * Get new relic app key.
     *
     * @return string
     */
    private function getAppKey()
    {
        return defined('NEW_RELIC_APP_KEY')
            ? NEW_RELIC_APP_KEY
            : $this->getAdditionalProperty('app_key');
    }

    /**
     * Set new relic app key.
     *
     * @param  string $value
     * @return string
     */
    public function setAppKey($value)
    {
        if (!AngieApplication::isOnDemand()) {
            $this->setAdditionalProperty('app_key', $value);
        }

        return $this->getAppKey();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'license' => $this->getLicense(),
            'app_key' => $this->getAppKey(),
        ]);
    }
}
