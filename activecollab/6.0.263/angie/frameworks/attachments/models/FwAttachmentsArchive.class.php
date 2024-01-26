<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Framework level archive attachments implementation.
 *
 * @package angie.frameworks.attachments
 * @subpackage models
 */
abstract class FwAttachmentsArchive implements JsonSerializable
{
    /**
     * @var IAttachments
     */
    protected $parent;

    /**
     * @var string
     */
    protected $archive_id;

    /**
     * Construct a new archive attachments.
     *
     * @param IAttachments $parent
     */
    public function __construct(IAttachments $parent)
    {
        $this->parent = $parent;
        $this->archive_id = md5(AngieApplication::authentication()->getLoggedUser()->getId() . '-' . DateTimeValue::now()->getTimestamp() . '-' . uniqid());
    }

    /**
     * Return archive id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->archive_id;
    }

    /**
     * Return file hash.
     *
     * @return string|null
     */
    public function getMd5()
    {
        $filepath = self::preparePathById($this->getId());

        if (is_file($filepath)) {
            return md5_file($filepath);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getParentType()
    {
        return strtolower(get_class($this->parent));
    }

    /**
     * Prepare download URL.
     *
     * @param  bool   $force
     * @return string
     */
    protected function prepareDownloadUrl($force = false)
    {
        return AngieApplication::getProxyUrl('download_attachments_archive', AttachmentsFramework::INJECT_INTO, [
            'id' => $this->getId(),
            'md5' => $this->getMd5(),
            'parent_type' => $this->getParentType(),
            'parent_id' => $this->parent->getId(),
            'force' => $force,
        ]);
    }

    /**
     * Return array or property => value pairs that describes this object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'download_url' => $this->prepareDownloadUrl(true),
        ];
    }

    /**
     * Return path to created archive.
     *
     * @param  string $archive_id
     * @return string
     */
    public static function getPath($archive_id)
    {
        return empty($archive_id) ? null : self::preparePathById($archive_id);
    }

    /**
     * Prepare archive.
     *
     * @return $this
     */
    public function prepare()
    {
        $archive_path = AngieApplication::getAvailableWorkFileName(uniqid('--PREPARING--') . '-' . DateTimeValue::now()->getTimestamp());
        $work_dir = AngieApplication::getAvailableDirName(WORK_PATH, 'batch_download_attachments');

        $paths = [];

        /** @var Attachment $attachment */
        foreach ($this->parent->getAttachments() as $attachment) {
            if ($attachment instanceof GoogleDriveAttachment) {
                continue;
            }

            if ($attachment->getDisposition() === IAttachments::ATTACHMENT) {
                $count = 1;
                $path_to_file = $work_dir . '/' . $attachment->getName();
                $path_parts = pathinfo($path_to_file);

                while (in_array($path_to_file, $paths)) {
                    $path_to_file = "$path_parts[dirname]/$path_parts[filename]($count).$path_parts[extension]";
                    ++$count;
                }

                $paths[] = $path_to_file;
                copy($attachment->getPath(), $path_to_file);
            }
        }

        $archive = new PclZip($archive_path);
        $v_list = $archive->create(implode(',', $paths), PCLZIP_OPT_REMOVE_PATH, $work_dir);

        delete_dir($work_dir);

        if ($v_list == 0) {
            throw new \RuntimeException($archive->errorInfo(true));
        }

        rename($archive_path, self::preparePathById($this->archive_id));

        return $this;
    }

    /**
     * Return path by archive id.
     *
     * @param  string $archive_id
     * @return string
     */
    protected static function preparePathById($archive_id)
    {
        return AngieApplication::getAvailableWorkFileName('attachments-archive-' . $archive_id, null, false);
    }
}
