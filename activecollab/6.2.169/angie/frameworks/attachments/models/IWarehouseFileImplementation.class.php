<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Warehouse file details implementation.
 *
 * @package angie.frameworks.attachments
 * @subpackage models
 */
trait IWarehouseFileImplementation
{
    /**
     * {@inheridoc}.
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['thumbnail_url'] = $this->getThumbnailUrl('--WIDTH--', '--HEIGHT--');
        $result['download_url'] = $this->getDownloadUrl(true);
        $result['preview_url'] = $this->getPreviewUrl();

        return $result;
    }

    /**
     * Return warehouse thumbnail url.
     *
     * @param  int    $width
     * @param  int    $height
     * @param  string $scale
     * @return string
     */
    public function getThumbnailUrl($width = 60, $height = 60, $scale = Thumbnails::SCALE)
    {
        return Integrations::findFirstByType(WarehouseIntegration::class)->prepareFileThumbnailUrl($this->getLocation(), $this->getMd5(), $width, $height);
    }

    /**
     * Return warehouse preview url.
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return Integrations::findFirstByType(WarehouseIntegration::class)->prepareFilePreviewUrl($this->getLocation(), $this->getMd5());
    }

    /**
     * Return warehouse download url.
     *
     * @param  bool   $force
     * @return string
     */
    public function getDownloadUrl($force = false)
    {
        return Integrations::findFirstByType(WarehouseIntegration::class)->prepareFileDownloadUrl($this->getLocation(), $this->getMd5(), $force);
    }

    /**
     * Return public download url.
     *
     * @param  bool   $force
     * @return string
     */
    public function getPublicDownloadUrl($force = false)
    {
        return $this->getDownloadUrl($force);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getLocation();

    /**
     * {@inheritdoc}
     */
    abstract public function getMd5();
}
