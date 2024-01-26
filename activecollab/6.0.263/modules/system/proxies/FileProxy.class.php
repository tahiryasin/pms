<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * Fw Preview Proxy.
 *
 * @package angie.frameworks.preview
 * @subpackage proxies
 */
abstract class FileProxy extends ProxyRequestHandler
{
    // Scaling method
    const SCALE = 'scale'; // Proportionally scale down to the given dimensions
    const CROP = 'crop'; // Crop from the middle of the image while forcing the full dimensions

    // Source types
    const SOURCE_IMAGE = 'image';
    const SOURCE_PDF = 'pdf';
    const SOURCE_PSD = 'psd';
    const SOURCE_OTHER = 'other';

    public function __construct()
    {
        require_once ANGIE_PATH . '/functions/general.php';
        require_once ANGIE_PATH . '/functions/web.php';
        require_once ANGIE_PATH . '/functions/files.php';
    }

    /**
     * Get file based on pieces of data.
     *
     * @param  string $context
     * @param  int    $id
     * @param  int    $size
     * @param  string $md5
     * @param  string $timestamp
     * @return array
     */
    protected function getFile($context, $id, $size, $md5, $timestamp)
    {
        if ($context === null || $id === null || $size === null || $md5 === null || $timestamp === null) {
            $this->badRequest();
        }

        $table_name = $this->contextToTableName($context);
        if (empty($table_name)) {
            $this->badRequest();
        }

        $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME); // connect to database

      if (empty($connection)) {
          $this->operationFailed();
      }

        $connection->set_charset('utf8');

        if ($table_name === 'project_template_elements') {
            $query = sprintf('SELECT name, raw_additional_properties FROM ' . $table_name . " WHERE id='%s' AND created_on='%s'",
              $connection->real_escape_string($id),
              $connection->real_escape_string($timestamp)
          );
        } else {
            // create query
          $query = sprintf('SELECT `type`, location, name, mime_type FROM ' . $table_name . " WHERE id='%s' AND size='%s' AND md5='%s' AND created_on='%s'",
              $connection->real_escape_string($id),
              $connection->real_escape_string($size),
              $connection->real_escape_string($md5),
              $connection->real_escape_string($timestamp)
          );
        }

      // extract file details
      $result = $connection->query($query);
        if ($result == false) {
            $this->notFound();
        }

        $file = $result->fetch_assoc();

        if (isset($file['raw_additional_properties'])) {
            $file = array_merge($file, unserialize($file['raw_additional_properties']));
            unset($file['raw_additional_properties']);

            if (!(isset($file['size']) && $file['size'] == $size)) {
                $this->notFound();
            }

            if (!(isset($file['md5']) && $file['md5'] == $md5)) {
                $this->notFound();
            }
        }

        if (!(isset($file['location']) && $file['location'])) {
            $this->notFound();
        }

        return $file;
    }

    /**
     * Generate thumbnail from image source.
     *
     * @param  string $source
     * @param  string $thumb_file
     * @param  int    $width
     * @param  int    $height
     * @param  string $scale
     * @return bool
     */
    protected function generateFromImage($source, $thumb_file, $width, $height, $scale)
    {
        try {
            if ($scale == self::SCALE) {
                scale_and_fit_image($source, $thumb_file, $width, $height, IMAGETYPE_JPEG, 100);
            } else {
                scale_and_crop_image_alt($source, $thumb_file, $width, $height, null, null, IMAGETYPE_JPEG, 100);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Generate a thumbnail from a PDF.
     *
     * @param  string $source
     * @param  string $thumb_file
     * @param  int    $width
     * @param  int    $height
     * @param  string $scale
     * @return bool
     */
    protected function generateFromPdf($source, $thumb_file, $width, $height, $scale)
    {
        if (!extension_loaded('imagick')) {
            return false;
        }

        try {
            $magic = new imagick(); // create imagick instance
            $magic->setResolution(200, 200); // set resolution before loading image
            $magic->readImage($source . '[0]'); // [0] means first page
            $magic->setimageformat('jpeg');
            $magic->setImageCompressionQuality(80);

            // Flatten image before resizing and if pdf has transparent background
            if (method_exists($magic, 'flattenImages')) {
                $magic = $magic->flattenImages();
            } else {
                $alphachannel_remove = defined('imagick::ALPHACHANNEL_REMOVE')
                    ? imagick::ALPHACHANNEL_REMOVE
                    : 11;

                $magic->setImageBackgroundColor('white');
                $magic->setImageAlphaChannel($alphachannel_remove);
                $magic->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            }

            // resize/crop image
            if ($scale == self::SCALE) {
                $magic->thumbnailimage($width, $height, true);
            } else {
                $magic->cropthumbnailimage($width, $height);
            }

            $magic->writeimage($thumb_file); // save image
            $magic->clear();
            $magic->destroy();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Generate a thumbnail from a PSD.
     *
     * @param  string $source
     * @param  string $thumb_file
     * @param  int    $width
     * @param  int    $height
     * @param  string $scale
     * @return bool
     */
    protected function generateFromPsd($source, $thumb_file, $width, $height, $scale)
    {
        if (!extension_loaded('imagick')) {
            return false;
        }

        try {
            $magic = new Imagick();
            $magic->setResolution(200, 200); // set resolution before loading image
            $magic->setBackgroundColor(new ImagickPixel('transparent'));
            $magic->readImage($source . '[0]');
            $magic->setimageformat('jpeg');
            $magic->setImageCompressionQuality(80);

            if ($scale == self::SCALE) {
                $magic->thumbnailimage($width, $height, true);
            } else {
                $magic->cropthumbnailimage($width, $height);
            }

            $magic->writeimage($thumb_file);
            $magic->clear();
            $magic->destroy();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Return source type based on source file and original name.
     *
     * @param  string $source_path
     * @param  string $original_name
     * @return string
     */
    protected function getSourceType($source_path, $original_name)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime_type = finfo_file($finfo, $source_path);

        if (in_array($mime_type, ['image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png'])) {
            return self::SOURCE_IMAGE;
        } elseif (in_array($mime_type, ['image/photoshop', 'image/x-photoshop', 'image/vnd.adobe.photoshop', 'image/psd', 'application/photoshop', 'application/psd'])) {
            return self::SOURCE_PSD;
        } elseif ($mime_type == 'application/pdf') {
            return self::SOURCE_PDF;
        } else {
            return self::SOURCE_OTHER;
        }
    }

    /**
     * @param  string      $context
     * @return string|null
     */
    protected function contextToTableName($context)
    {
        return $context === 'attachments' ? 'attachments' : null;
    }
}
