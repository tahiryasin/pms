<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Http\Request;
use Psr\Http\Message\UploadedFileInterface;
use Zend\Diactoros\UploadedFile as ZendUploadedFile;

AngieApplication::useController('auth_not_required', EnvironmentFramework::INJECT_INTO);

/**
 * Framework level upload files controller.
 *
 * @package angie.frameworks.environment
 * @subpackage controllers
 */
abstract class FwUploadFilesController extends AuthNotRequiredController
{
    /**
     * Handle file uploads.
     *
     * @param  Request     $request
     * @return array
     * @throws UploadError
     */
    public function index(Request $request)
    {
        $result = [];

        $uploaded_files = $request->getUploadedFiles();

        $file = null;

        foreach (['file', 'attachment_1'] as $key_to_check) {
            if (array_key_exists($key_to_check, $uploaded_files)) {
                $file = $uploaded_files[$key_to_check];
                break;
            }
        }

        if (!$file instanceof UploadedFileInterface) {
            return [];
        }

        $error_code_or_message = $file->getError();

        if (!empty($error_code_or_message)) {
            throw new UploadError($this->convertCodeToMessage($error_code_or_message));
        }

        $uploaded_file = UploadedFiles::addUploadedFile(
            [
                'name' => $file->getClientFilename(),
                'type' => $file->getClientMediaType(),
                'tmp_name' => $this->extractTmpName($file),
                'error' => $error_code_or_message,
                'size' => $file->getSize(),
            ]
        );
        if ($uploaded_file instanceof UploadedFile) {
            $result[] = $uploaded_file;
        }

        return $result;
    }

    /**
     * Prepare upload instructions.
     *
     * @return array
     */
    public function prepare()
    {
        /** @var WarehouseIntegration $warehouse_integration */
        $warehouse_integration = Integrations::findFirstByType(WarehouseIntegration::class);

        if ($warehouse_integration->isInUse()) {
            return $warehouse_integration->prepareForUpload();
        }

        return [
            'upload_url' => ROOT_URL . '/api/v1/upload-files',
            'pingback_url' => null,
            'intent' => null,
        ];
    }

    /**
     * Convert upload code to message.
     *
     * Note: Some PSR-7 implementation return actual error message instead of the code, so we are handling that as well.
     *
     * @param  int|string $code_or_message
     * @return string
     */
    private function convertCodeToMessage($code_or_message): string
    {
        if (is_int($code_or_message) || ctype_digit($code_or_message)) {
            switch ($code_or_message) {
                case UPLOAD_ERR_INI_SIZE:
                    return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                case UPLOAD_ERR_FORM_SIZE:
                    return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                case UPLOAD_ERR_PARTIAL:
                    return 'The uploaded file was only partially uploaded';
                case UPLOAD_ERR_NO_FILE:
                    return 'No file was uploaded';
                case UPLOAD_ERR_NO_TMP_DIR:
                    return 'Missing a temporary folder';
                case UPLOAD_ERR_CANT_WRITE:
                    return 'Failed to write file to disk';
                case UPLOAD_ERR_EXTENSION:
                    return 'File upload stopped by extension';
                default:
                    return 'Unknown upload error';
            }
        } elseif ($code_or_message) {
            return (string) $code_or_message;
        }

        return 'Unknown upload error';
    }

    /**
     * We need this hack because Zend UploadedFile doesn't have public API for tmp name.
     *
     * @param  ZendUploadedFile|UploadedFileInterface $file
     * @return string
     */
    private function extractTmpName(ZendUploadedFile $file)
    {
        $property = (new ReflectionClass(ZendUploadedFile::class))->getProperty('file');
        $property->setAccessible(true);
        $tmp_name = $property->getValue($file);
        $property->setAccessible(false);

        return $tmp_name;
    }
}
