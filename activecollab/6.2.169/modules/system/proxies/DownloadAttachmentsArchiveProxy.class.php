<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

require_once __DIR__ . '/FileProxy.class.php';

class DownloadAttachmentsArchiveProxy extends FileProxy
{
    /**
     * Id of attachments archive were going to download.
     *
     * @var int
     */
    private $id;

    /**
     * hash of the file.
     *
     * @var string
     */
    private $md5;

    /**
     * parent id.
     *
     * @var string
     */
    private $parent_id;

    /**
     * parent type.
     *
     * @var string
     */
    private $parent_type;

    /**
     * Force download.
     *
     * @var bool
     */
    private $force;

    /**
     * Construct proxy request handler.
     *
     * @param array $params
     */
    public function __construct($params = null)
    {
        parent::__construct();

        $this->id = isset($params['id']) && $params['id'] ? trim($params['id']) : null;
        $this->md5 = isset($params['md5']) && $params['md5'] ? $params['md5'] : null;
        $this->parent_type = isset($params['parent_type']) && $params['parent_type'] ? $params['parent_type'] : null;
        $this->parent_id = isset($params['parent_id']) && $params['parent_id'] ? $params['parent_id'] : null;
        $this->force = isset($params['force']) && $params['force'];
    }

    public function execute()
    {
        $file_path = $this->getAvailableFileName(
            WORK_PATH,
            'attachments-archive-' . $this->id,
            null,
            false
        );

        if (!is_file($file_path)) {
            $this->notFound();
        }

        $mime_type = 'application/zip';

        header('Content-type: ' . $mime_type);
        header('Cache-Control: public, max-age=315360000');
        header('Pragma: public');
        header('Etag: ' . $this->md5);

        $cached_hash = $this->getCachedEtag();

        if ($cached_hash && $cached_hash == $this->md5) {
            $this->notModified();
        }

        $time = time();
        $filename = "attachments-{$this->parent_type}-{$this->parent_id}-{$time}.zip";

        download_file($file_path, $mime_type, $filename, $this->force, true, true);
    }
}
