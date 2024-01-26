<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * @package ActiveCollab.modules.system
 * @subpackage model
 */
class DesktopAppIntegration extends Integration
{
    /**
     * @var string
     */
    private $shepherd_prefix = 'https://activecollab.com';

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
    public function isInUse(User $user = null)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Desktop Apps (Beta)';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortName()
    {
        return 'desktop-app';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return lang('Run ActiveCollab as an app on your Mac or Windows computer.');
    }

    /**
     * @return string
     */
    public function getWindowsDownloadUrl()
    {
        return $this->shepherd_prefix . '/api/v2/desktop-apps/activecollab/releases/win32/download';
    }

    /**
     * @return string
     */
    public function getMacDownloadUrl()
    {
        return $this->shepherd_prefix . '/api/v2/desktop-apps/activecollab/releases/darwin/download';
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'download_urls' => [
                'mac' => $this->getMacDownloadUrl(),
                'windows' => $this->getWindowsDownloadUrl(),
            ],
        ]);
    }
}
