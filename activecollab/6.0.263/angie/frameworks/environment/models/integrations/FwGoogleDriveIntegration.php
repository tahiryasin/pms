<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Google Drive integrations class.
 *
 * @package ActiveCollab.modules.system
 * @subpackage models
 */
abstract class FwGoogleDriveIntegration extends Integration
{
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
     * Return tru if this integration is in use.
     *
     * @param  User $user
     * @return bool
     */
    public function isInUse(User $user = null)
    {
        return $this->getClientId() && $this->getClientSecret();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Google Drive';
    }

    /**
     * Return integration short name.
     *
     * @return string
     */
    public function getShortName()
    {
        return 'google-drive';
    }

    /**
     * Return short integration description.
     *
     * @return string
     */
    public function getDescription()
    {
        return lang('Attach files from your Google Drive');
    }

    /**
     * Return client id.
     *
     * @return string|null
     */
    public function getClientId()
    {
        return defined('GOOGLE_DRIVE_CLIENT_ID') && AngieApplication::isOnDemand()
            ? GOOGLE_DRIVE_CLIENT_ID
            : $this->getAdditionalProperty('client_id');
    }

    /**
     * Set client id.
     *
     * @param  string      $client_id
     * @return string|null
     */
    public function setClientId($client_id)
    {
        if (!AngieApplication::isOnDemand()) {
            $this->setAdditionalProperty('client_id', $client_id);
        }

        return self::getClientId();
    }

    /**
     * Return client secret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return defined('GOOGLE_DRIVE_CLIENT_SECRET') && AngieApplication::isOnDemand()
            ? GOOGLE_DRIVE_CLIENT_SECRET
            : $this->getAdditionalProperty('client_secret');
    }

    /**
     * Set client secret.
     *
     * @param  string $client_secret
     * @return string
     */
    public function setClientSecret($client_secret)
    {
        if (!AngieApplication::isOnDemand()) {
            $this->setAdditionalProperty('client_secret', $client_secret);
        }

        return self::getClientSecret();
    }

    /**
     * Return app id.
     *
     * @return string|null
     */
    public function getAppId()
    {
        $result = explode('-', $this->getClientId());

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['client_id'] = $this->getClientId();
        $result['app_id'] = $this->getAppId();

        return $result;
    }

    /**
     * Create UploadedFile from each picked Google Drive file and return result.
     *
     * @param  array $docs
     * @return array
     */
    public function onBatchAdd(array $docs = [])
    {
        $result = [];

        DB::transact(
            function () use ($docs, &$result) {
                foreach ($docs as $doc) {
                    $attributes = [
                        'type' => GoogleDriveUploadedFile::class,
                        'name' => $doc['name'],
                        'mime_type' => $doc['mimeType'],
                        'size' => $doc['sizeBytes'],
                        'location' => date('Y-m') . '/' . make_string(40),
                    ];

                    $file = UploadedFiles::create($attributes, false);

                    if ($file instanceof GoogleDriveUploadedFile && isset($doc['url'])) {
                        $file->setUrl($doc['url']);
                    }

                    $file->save();

                    $result[] = $file;
                }
            },
            'Create google drive uploaded files'
        );

        return $result;
    }

    public function save()
    {
        try {
            DB::beginWork('Saving google drive integration @ ' . __CLASS__);

            parent::save();

            AngieApplication::initialSettingsCacheInvalidator()->invalidateInitialSettingsCache();
            AngieApplication::cache()->remove('config_options');

            DB::commit('Google drive integration saved @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to save google drive integration @ ' . __CLASS__);
            throw $e;
        }
    }

    public function delete($bulk = false)
    {
        try {
            DB::beginWork('Deleting google drive integration @ ' . __CLASS__);

            parent::delete($bulk);

            AngieApplication::initialSettingsCacheInvalidator()->invalidateInitialSettingsCache();
            AngieApplication::cache()->remove('config_options');

            DB::commit('Google drive integration deleted @ ' . __CLASS__);
        } catch (Exception $e) {
            DB::rollback('Failed to delete google drive integration @ ' . __CLASS__);
            throw $e;
        }
    }
}
